<?php
require 'C:\laragon\www\admision\vendor\autoload.php';
$app = require_once 'C:\laragon\www\admision\bootstrap\app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Try reading and executing the SQL file in chunks
$file = 'C:\laragon\www\admision\backup_restore.sql';
$handle = fopen($file, 'r');
if (!$handle) {
    die("Cannot open file\n");
}

DB::statement("SET FOREIGN_KEY_CHECKS=0");

$buffer = '';
$count = 0;
$size = 0;
while (($line = fgets($handle)) !== false) {
    $buffer .= $line;
    $size += strlen($line);
    
    // Check if we have a complete statement
    if (substr(trim($line), -1) === ';' && $size > 100000) {
        try {
            DB::unprepared($buffer);
            $count++;
        } catch (\Exception $e) {
            // skip errors
        }
        $buffer = '';
        $size = 0;
        
        if ($count % 100 == 0) {
            echo "Executed $count statements...\n";
        }
    }
}

// Execute remaining
if (trim($buffer)) {
    try {
        DB::unprepared($buffer);
        $count++;
    } catch (\Exception $e) {
        // skip
    }
}

fclose($handle);
DB::statement("SET FOREIGN_KEY_CHECKS=1");

echo "Done. Executed ~$count statements.\n";

$tables = DB::select("SHOW TABLES");
echo "Tables now: " . count($tables) . "\n";
