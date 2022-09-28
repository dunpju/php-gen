<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Utils;


class CamelizeUtil
{
    /**
     * 下划线转驼峰
     * @param string $uncamelizedWords
     * @param string $separator
     * @return string
     */
    public static function camelize(string $uncamelizedWords, string $separator = '_'): string
    {
        if (!preg_match("/([a-z])([A-Z])/", $uncamelizedWords)) {
            $uncamelizedWords = $separator . str_replace($separator, " ", strtolower($uncamelizedWords));
            return ltrim(str_replace(" ", "", ucwords($uncamelizedWords)), $separator);
        } else {
            return $uncamelizedWords;
        }
    }

    /**
     * 驼峰命名转下划线命名
     * @param string $camelCaps
     * @param string $separator
     * @return string
     */
    public static function uncamelize(string $camelCaps, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}