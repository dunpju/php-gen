<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Utils;


class MkdirUtil
{
    /**
     * @param $dir
     * @return bool
     */
    public static function dir($dir)
    {
        return is_dir($dir) or (self::dir(dirname($dir)) and mkdir($dir, 0777));
    }
}