<?php
namespace App\Core\Utils;

use Exception;
use ZipArchive;

/**
 * 创建压缩文件
 */
class Zipper
{
    // 压缩文件夹
    public function zip(string $filename, string $directory)
    {
        $zip = new ZipArchive();
        if ($zip->open($filename, ZipArchive::CREATE|ZipArchive::OVERWRITE) === true) {
            $this->addToZip($zip, $directory, $directory);
            $zip->close();
        } else {
            throw new Exception("Build zip file failed!");
        }
    }

    // 解压
    public function unzip(string $filename, string $directory)
    {
        $zip = new ZipArchive;
        if ($zip->open($filename) === true) {
            $zip->extractTo($directory);
            $zip->close();
        } else {
            throw new Exception("Open zip file failed!");
        }
    }

    // 添加文件
    private function addToZip(ZipArchive $zip, string $directory, string $basePath)
    {
        $directory = rtrim($directory, "/");
        foreach (glob($directory . "/*") as $file) {
            if (is_dir($file)) {
                $zip = $this->addToZip($zip, $file, $basePath);
            } else {
                $zip->addFile($file, ltrim(str_replace($basePath, '', $file), "/"));
            }
        }
        return $zip;
    }
}
