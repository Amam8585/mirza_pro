<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function.php';
require_once __DIR__ . '/../botapi.php';

function addPathToZip(ZipArchive $zip, $path, $basePath)
{
    $normalizedBase = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    if (is_dir($path)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = (string) $file;
            $relativePath = ltrim(str_replace($normalizedBase, '', $filePath), DIRECTORY_SEPARATOR);

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } elseif ($file->isFile()) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    } elseif (is_file($path)) {
        $relativePath = ltrim(str_replace($normalizedBase, '', $path), DIRECTORY_SEPARATOR);
        $zip->addFile($path, $relativePath);
    }
}

$reportbackup = select("topicid","idreport","report","backupfile","select")['idreport'];
$destination = getcwd();
$setting = select("setting", "*");
$sourcefir = dirname($destination);
$botlist = select("botsaz","*",null,null,"fetchAll");
if ($botlist) {
    foreach ($botlist as $bot) {
        $folderName = $bot['id_user'] . $bot['username'];
        $botBasePath = $sourcefir . '/vpnbot/' . $folderName;
        $zipFilePath = $destination . '/file_' . $folderName . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $pathsToBackup = [
                $botBasePath . '/data',
                $botBasePath . '/product.json',
                $botBasePath . '/product_name.json',
            ];

            foreach ($pathsToBackup as $path) {
                if (file_exists($path)) {
                    addPathToZip($zip, $path, $botBasePath . '/');
                } else {
                    error_log('Backup path not found for bot archive: ' . $path);
                }
            }
            $zip->close();

            telegram('sendDocument', [
                'chat_id' => $setting['Channel_Report'],
                'message_thread_id' => $reportbackup,
                'document' => new CURLFile($zipFilePath),
                'caption' => "@{$bot['username']} | {$bot['id_user']}",
            ]);

            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
            }
        } else {
            error_log('Unable to create zip archive for bot directory: ' . $botBasePath);
        }
    }
}




$backup_file_name = 'backup_' . date("Y-m-d") . '.sql';
$zip_file_name = 'backup_' . date("Y-m-d") . '.zip';

$command = "mysqldump -h localhost -u $usernamedb -p'$passworddb' --no-tablespaces $dbname > $backup_file_name";

$output = [];
$return_var = 0;
exec($command, $output, $return_var);
if ($return_var !== 0) {
    telegram('sendmessage', [
        'chat_id' => $setting['Channel_Report'],
        'message_thread_id' => $reportbackup,
        'text' => "❌❌❌❌❌❌خطا در بکاپ گرفتن لطفا به پشتیبانی اطلاع دهید",
    ]);
} else {
$zip = new ZipArchive();
if ($zip->open($zip_file_name, ZipArchive::CREATE) === TRUE) {
    $zip->addFile($backup_file_name, basename($backup_file_name));
    $zip->close();

    telegram('sendDocument', [
        'chat_id' => $setting['Channel_Report'],
        'message_thread_id' => $reportbackup,
        'document' => new CURLFile($zip_file_name),
        'caption' => "📌 خروجی دیتابیس ربات اصلی",
    ]);
    unlink($zip_file_name);
    unlink($backup_file_name);
}
}
