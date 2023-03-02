<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Utils;


class DirUtil
{
    /**
     * @param $dir
     * @param int $permissions
     * @return bool
     */
    public static function mkdir($dir, $permissions = 0777): bool
    {
        return is_dir($dir) or (self::mkdir(dirname($dir)) and mkdir($dir, $permissions));
    }

    /**
     * @param $dirPath
     * @param false $delParent
     * @return bool
     */
    public static function empty(string $dirPath, bool $delParent = false): bool
    {
        if (!file_exists($dirPath)) {
            return false;
        }
        if (is_file($dirPath)) {
            unlink($dirPath);
        }
        $files = scandir($dirPath);
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $path = $dirPath . '/' . $file;
                    if (is_file($path)) {
                        unlink($path);
                    } else {
                        self::empty($path);
                        rmdir($path);
                    }
                }
            }
        }
        if ($delParent) {
            return rmdir($dirPath);
        }
        return true;
    }
}