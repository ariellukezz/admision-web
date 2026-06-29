<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    private const DISK = 'local';
    private const BACKUP_DIR = 'backups';

    public function index()
    {
        $backups = [];
        $files = Storage::disk(self::DISK)->files(self::BACKUP_DIR);

        foreach ($files as $file) {
            if (!str_ends_with($file, '.sql') && !str_ends_with($file, '.sql.gz')) {
                continue;
            }

            $size = Storage::disk(self::DISK)->size($file);
            $lastModified = Storage::disk(self::DISK)->lastModified($file);

            $backups[] = [
                'file'       => basename($file),
                'path'       => $file,
                'size'       => $this->formatSize($size),
                'size_bytes' => $size,
                'date'       => Carbon::createFromTimestamp($lastModified)->format('d/m/Y H:i:s'),
                'timestamp'  => $lastModified,
                'compressed' => str_ends_with($file, '.gz'),
            ];
        }

        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        // Info de la base de datos
        $dbInfo = $this->getDatabaseInfo();

        return response()->json([
            'success'  => true,
            'backups'  => $backups,
            'db_info'  => $dbInfo,
            'disk_free' => $this->formatSize(disk_free_space(storage_path())),
            'disk_total' => $this->formatSize(disk_total_space(storage_path())),
        ]);
    }

    public function crear(Request $request)
    {
        $request->validate([
            'comprimir' => 'nullable|boolean',
        ]);

        $comprimir = $request->comprimir ?? true;

        try {
            $this->ensureBackupDirectory();

            $connection = config('database.default');
            $dbConfig = config("database.connections.{$connection}");

            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            $filepath = self::BACKUP_DIR . '/' . $filename;
            $fullPath = Storage::disk(self::DISK)->path($filepath);

            // Construir comando mysqldump optimizado para BD grandes
            $command = $this->buildMysqlDumpCommand($dbConfig, $fullPath);

            $process = Process::fromShellCommandline($command);
            $process->setTimeout(1800); // 30 minutos máximo
            $process->run();

            if (!$process->isSuccessful()) {
                // Limpiar archivo parcial si falló
                if (Storage::disk(self::DISK)->exists($filepath)) {
                    Storage::disk(self::DISK)->delete($filepath);
                }
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al crear respaldo: ' . mb_substr($process->getErrorOutput(), 0, 500),
                ], 500);
            }

            // Comprimir con gzip si se solicitó
            if ($comprimir && function_exists('gzopen')) {
                $this->compressFile($filepath, $filepath . '.gz');
                Storage::disk(self::DISK)->delete($filepath);
                $filepath = $filepath . '.gz';
            }

            $size = Storage::disk(self::DISK)->size($filepath);

            return response()->json([
                'success' => true,
                'mensaje' => 'Respaldo creado correctamente',
                'datos'   => [
                    'file' => basename($filepath),
                    'size' => $this->formatSize($size),
                    'date' => Carbon::now()->format('d/m/Y H:i:s'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function descargar($filename)
    {
        $path = self::BACKUP_DIR . '/' . $filename;

        if (!Storage::disk(self::DISK)->exists($path)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Archivo no encontrado',
            ], 404);
        }

        $fullPath = Storage::disk(self::DISK)->path($path);
        $mimeType = str_ends_with($filename, '.gz') ? 'application/gzip' : 'text/plain';

        return response()->download($fullPath, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function restaurar(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $filename = $request->filename;
        $path = self::BACKUP_DIR . '/' . $filename;

        if (!Storage::disk(self::DISK)->exists($path)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Archivo de respaldo no encontrado',
            ], 404);
        }

        try {
            $connection = config('database.default');
            $dbConfig = config("database.connections.{$connection}");

            $fullPath = Storage::disk(self::DISK)->path($path);

            // Crear respaldo de seguridad antes de restaurar
            $this->crearAntesDeRestaurar();

            // Descomprimir si es .gz
            $sqlFile = $fullPath;
            if (str_ends_with($filename, '.gz')) {
                $sqlFile = $this->decompressFile($path);
                if (!$sqlFile) {
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'Error al descomprimir el archivo',
                    ], 500);
                }
            }

            // Restaurar usando mysql client
            $command = $this->buildMysqlRestoreCommand($dbConfig, $sqlFile);

            $process = Process::fromShellCommandline($command);
            $process->setTimeout(3600); // 60 minutos máximo para restaurar
            $process->run();

            // Limpiar archivo temporal descomprimido
            if (str_ends_with($filename, '.gz') && $sqlFile !== $fullPath) {
                @unlink($sqlFile);
            }

            if (!$process->isSuccessful()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al restaurar: ' . mb_substr($process->getErrorOutput(), 0, 500),
                ], 500);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Base de datos restaurada correctamente. Se creó un respaldo de seguridad previo.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function eliminar(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $path = self::BACKUP_DIR . '/' . $request->filename;

        if (!Storage::disk(self::DISK)->exists($path)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Archivo no encontrado',
            ], 404);
        }

        Storage::disk(self::DISK)->delete($path);

        return response()->json([
            'success' => true,
            'mensaje' => 'Respaldo eliminado correctamente',
        ]);
    }

    // ─── MÉTODOS PRIVADOS ────────────────────────────────────────────

    private function ensureBackupDirectory(): void
    {
        if (!Storage::disk(self::DISK)->exists(self::BACKUP_DIR)) {
            Storage::disk(self::DISK)->makeDirectory(self::BACKUP_DIR);
        }
    }

    private function buildMysqlDumpCommand(array $dbConfig, string $outputPath): string
    {
        $host = escapeshellarg($dbConfig['host'] ?? '127.0.0.1');
        $port = escapeshellarg($dbConfig['port'] ?? 3306);
        $user = escapeshellarg($dbConfig['username'] ?? '');
        $database = escapeshellarg($dbConfig['database'] ?? '');
        $output = escapeshellarg($outputPath);

        $password = $dbConfig['password'] ?? '';
        $passwordPart = $password !== ''
            ? 'MYSQL_PWD=' . escapeshellarg($password) . ' '
            : '';

        // Flags optimizadas para BD grandes
        return $passwordPart . "mysqldump "
            . "--user={$user} "
            . "--host={$host} "
            . "--port={$port} "
            . "--single-transaction "
            . "--routines "
            . "--triggers "
            . "--quick "
            . "--lock-tables=false "
            . "--max-allowed-packet=512M "
            . "--net-buffer-length=32768 "
            . "{$database} > {$output} 2>&1";
    }

    private function buildMysqlRestoreCommand(array $dbConfig, string $sqlFile): string
    {
        $host = escapeshellarg($dbConfig['host'] ?? '127.0.0.1');
        $port = escapeshellarg($dbConfig['port'] ?? 3306);
        $user = escapeshellarg($dbConfig['username'] ?? '');
        $database = escapeshellarg($dbConfig['database'] ?? '');
        $input = escapeshellarg($sqlFile);

        $password = $dbConfig['password'] ?? '';
        $passwordPart = $password !== ''
            ? 'MYSQL_PWD=' . escapeshellarg($password) . ' '
            : '';

        return $passwordPart . "mysql "
            . "--user={$user} "
            . "--host={$host} "
            . "--port={$port} "
            . "--max-allowed-packet=512M "
            . "{$database} < {$input} 2>&1";
    }

    private function crearAntesDeRestaurar(): void
    {
        $this->ensureBackupDirectory();

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "pre_restore_{$timestamp}.sql";
        $filepath = self::BACKUP_DIR . '/' . $filename;
        $fullPath = Storage::disk(self::DISK)->path($filepath);

        $command = $this->buildMysqlDumpCommand($dbConfig, $fullPath);

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(1800);
        $process->run();
    }

    private function compressFile(string $sourcePath, string $destPath): void
    {
        $sourceFull = Storage::disk(self::DISK)->path($sourcePath);
        $destFull = Storage::disk(self::DISK)->path($destPath);

        $sourceHandle = fopen($sourceFull, 'rb');
        $destHandle = gzopen($destFull, 'wb9');

        while (!feof($sourceHandle)) {
            gzwrite($destHandle, fread($sourceHandle, 1048576)); // 1MB chunks
        }

        fclose($sourceHandle);
        gzclose($destHandle);
    }

    private function decompressFile(string $gzPath): ?string
    {
        $gzFull = Storage::disk(self::DISK)->path($gzPath);
        $sqlFilename = str_replace('.gz', '', basename($gzPath));
        $sqlTempPath = sys_get_temp_dir() . '/' . $sqlFilename;

        $sourceHandle = gzopen($gzFull, 'rb');
        $destHandle = fopen($sqlTempPath, 'wb');

        if (!$sourceHandle || !$destHandle) {
            return null;
        }

        while (!gzeof($sourceHandle)) {
            fwrite($destHandle, gzread($sourceHandle, 1048576));
        }

        gzclose($sourceHandle);
        fclose($destHandle);

        return $sqlTempPath;
    }

    private function getDatabaseInfo(): array
    {
        try {
            $connection = config('database.default');
            $dbConfig = config("database.connections.{$connection}");
            $database = $dbConfig['database'] ?? '';

            $size = DB::selectOne("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$database]);

            $tables = DB::selectOne("
                SELECT COUNT(*) AS total
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$database]);

            return [
                'database'  => $database,
                'size_mb'   => $size->size_mb ?? 0,
                'tables'    => $tables->total ?? 0,
                'host'      => $dbConfig['host'] ?? '127.0.0.1',
            ];
        } catch (\Exception $e) {
            return [
                'database'  => config('database.connections.mysql.database', ''),
                'size_mb'   => 0,
                'tables'    => 0,
                'host'      => '127.0.0.1',
            ];
        }
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
