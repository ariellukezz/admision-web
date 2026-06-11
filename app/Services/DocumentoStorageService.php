<?php

namespace App\Services;

use App\Models\Documento;
use App\Models\DocumentoAudit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoStorageService
{
    private string $disk = 'documentos';

    private int $maxUploadMB = 20;

    private array $allowedExtensions = [
        'pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx', 'xls', 'xlsx',
    ];

    public function __construct()
    {
        $this->ensureDiskExists();
    }

    /**
     * Subir archivo con deduplicación por hash (estilo documentos_service).
     * Si el hash ya existe, no se guarda archivo físico nuevo, solo se crea registro.
     */
    public function saveFile(
        UploadedFile $file,
        int $userId,
        ?int $postulanteId = null,
        ?int $tipoDocumentoId = null,
        ?string $codigo = null,
    ): Documento {
        $data = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $data);
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $size = $file->getSize();
        $originalName = $file->getClientOriginalName();

        if (empty($extension)) {
            throw new \InvalidArgumentException('El archivo debe tener una extensión');
        }

        if (!in_array($extension, $this->allowedExtensions)) {
            throw new \InvalidArgumentException("Extensión no permitida: {$extension}");
        }

        if ($size > $this->maxUploadMB * 1024 * 1024) {
            throw new \InvalidArgumentException("El archivo excede el tamaño máximo ({$this->maxUploadMB} MB)");
        }

        // Deduplicación: buscar si ya existe un archivo con el mismo hash
        $existing = Documento::where('hash', $hash)->where('is_deleted', false)->first();

        $path = $existing?->path;

        if (!$existing || !$path) {
            $path = $this->buildStoragePath($hash, $extension);
            Storage::disk($this->disk)->put($path, $data);
        }

        if (!$codigo) {
            $codigo = 'DOC-' . ($postulanteId ?? '0') . '-' . time();
        }

        $documento = Documento::create([
            'codigo' => $codigo,
            'nombre' => $originalName,
            'id_postulante' => $postulanteId,
            'id_tipo_documento' => $tipoDocumentoId,
            'estado' => 1,
            'url' => $path,
            'numero' => 1,
            'id_usuario' => $userId,
            'extension' => $extension,
            'mime' => $mime,
            'size' => $size,
            'path' => $path,
            'hash' => $hash,
            'is_deleted' => false,
            'version' => 1,
        ]);

        $this->registerAudit($documento->id, 'UPLOAD');

        return $documento;
    }

    /**
     * Listar documentos paginados (no eliminados).
     */
    public function getFiles(int $page = 1, int $limit = 20, ?int $postulanteId = null, ?int $tipoDocumentoId = null): array
    {
        $query = Documento::notDeleted()->with(['tipoDocumento', 'postulante']);

        if ($postulanteId) {
            $query->where('id_postulante', $postulanteId);
        }

        if ($tipoDocumentoId) {
            $query->where('id_tipo_documento', $tipoDocumentoId);
        }

        $total = $query->count();
        $data = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'data' => $data,
        ];
    }

    /**
     * Obtener documento por ID.
     */
    public function getFile(int $id): ?Documento
    {
        return Documento::notDeleted()->with(['tipoDocumento', 'postulante'])->find($id);
    }

    /**
     * Descargar archivo físico.
     */
    public function downloadFile(int $id): StreamedResponse
    {
        $documento = $this->getFile($id);

        if (!$documento || !$documento->path) {
            throw new \RuntimeException('Archivo no encontrado');
        }

        if (!Storage::disk($this->disk)->exists($documento->path)) {
            throw new \RuntimeException('Archivo físico no encontrado');
        }

        $this->registerAudit($documento->id, 'DOWNLOAD');

        return Storage::disk($this->disk)->download($documento->path, $documento->nombre);
    }

    /**
     * Obtener archivo para vista previa inline (no descarga).
     */
    public function previewFile(int $id)
    {
        $documento = Documento::notDeleted()->find($id);

        if (!$documento || !$documento->path) {
            throw new \RuntimeException('Archivo no encontrado');
        }

        if (!Storage::disk($this->disk)->exists($documento->path)) {
            throw new \RuntimeException('Archivo físico no encontrado');
        }

        $this->registerAudit($documento->id, 'VIEW');

        $mimeType = $documento->mime ?: 'application/pdf';

        return Storage::disk($this->disk)->response($documento->path, null, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Actualizar (reemplazar) archivo de un documento.
     * Si el hash es igual al anterior, se rechaza.
     * Si otros registros usan el hash anterior, se conserva el archivo físico.
     */
    public function updateFile(int $id, UploadedFile $file, int $userId): Documento
    {
        $documento = Documento::notDeleted()->find($id);

        if (!$documento) {
            throw new \RuntimeException('Documento no encontrado');
        }

        $data = file_get_contents($file->getRealPath());
        $newHash = hash('sha256', $data);
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $size = $file->getSize();
        $originalName = $file->getClientOriginalName();

        if ($newHash === $documento->hash) {
            throw new \InvalidArgumentException('El archivo es idéntico al ya almacenado');
        }

        $linkedCount = $this->countRecordsByHash($documento->hash);

        $existingNew = Documento::where('hash', $newHash)->where('is_deleted', false)->first();
        $newPath = $existingNew?->path;

        if (!$newPath) {
            $newPath = $this->buildStoragePath($newHash, $extension);
            Storage::disk($this->disk)->put($newPath, $data);
        }

        // Si el archivo anterior solo lo usa este registro, eliminar físico
        if ($linkedCount <= 1 && $documento->path && !$existingNew) {
            $oldPath = $documento->path;
            // Verificar que ningún otro registro (incluidos eliminados) use este path
            $pathUsers = Documento::where('path', $oldPath)->where('id', '!=', $documento->id)->count();
            if ($pathUsers === 0) {
                Storage::disk($this->disk)->delete($oldPath);
            }
        }

        $documento->update([
            'nombre' => $originalName,
            'extension' => $extension,
            'mime' => $mime,
            'size' => $size,
            'path' => $newPath,
            'hash' => $newHash,
            'url' => $newPath,
            'version' => $documento->version + 1,
        ]);

        $this->registerAudit($documento->id, 'UPDATE');

        return $documento->fresh();
    }

    /**
     * Eliminar documento.
     * Si otros registros usan el mismo hash, solo soft-delete.
     * Si es el único, se elimina también el archivo físico.
     */
    public function deleteFile(int $id, int $userId): array
    {
        $documento = Documento::notDeleted()->find($id);

        if (!$documento) {
            throw new \RuntimeException('Documento no encontrado');
        }

        $linkedCount = $this->countRecordsByHash($documento->hash);
        $msg = '';

        if ($linkedCount > 1) {
            // Soft-delete: otros registros usan el mismo archivo físico
            $documento->update(['is_deleted' => true]);
            $msg = 'Registro eliminado, archivo físico conservado (otros registros lo usan)';
        } else {
            // Hard-delete: es el único registro que usa el archivo
            if ($documento->path) {
                $pathUsers = Documento::where('path', $documento->path)->where('id', '!=', $documento->id)->count();
                if ($pathUsers === 0) {
                    Storage::disk($this->disk)->delete($documento->path);
                }
            }
            $documento->update(['is_deleted' => true]);
            $msg = 'Archivo y registro eliminados correctamente';
        }

        $this->registerAudit($documento->id, 'DELETE');

        return ['message' => $msg];
    }

    /**
     * Verificar/rechazar documento.
     */
    public function verifyFile(int $id, int $verificado): Documento
    {
        $documento = Documento::find($id);

        if (!$documento) {
            throw new \RuntimeException('Documento no encontrado');
        }

        $documento->update([
            'verificado' => $verificado,
            'id_usuario' => Auth::id(),
        ]);

        $this->registerAudit($documento->id, 'VERIFY');

        return $documento->fresh();
    }

    /**
     * Contar registros activos que usan el mismo hash.
     */
    private function countRecordsByHash(?string $hash): int
    {
        if (!$hash) {
            return 0;
        }
        return Documento::where('hash', $hash)->where('is_deleted', false)->count();
    }

    /**
     * Construir ruta de almacenamiento: YYYY/MM/DD/{hash}.{ext}
     */
    private function buildStoragePath(string $hash, string $extension): string
    {
        $now = now();

        return implode('/', [
            $now->format('Y'),
            $now->format('m'),
            $now->format('d'),
            "{$hash}.{$extension}",
        ]);
    }

    /**
     * Registrar acción en auditoría.
     */
    private function registerAudit(int $documentoId, string $action): void
    {
        try {
            DocumentoAudit::create([
                'user_id' => Auth::id(),
                'documento_id' => $documentoId,
                'action' => $action,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('Error registrando auditoría documento: ' . $e->getMessage());
        }
    }

    /**
     * Asegurar que el disk de documentos existe en la configuración.
     */
    private function ensureDiskExists(): void
    {
        if (!config("filesystems.disks.{$this->disk}")) {
            // Si no está configurado, usaremos el disk 'local' con subcarpeta
            $this->disk = 'local';
        }
    }

    public function getDisk(): string
    {
        return $this->disk;
    }
}
