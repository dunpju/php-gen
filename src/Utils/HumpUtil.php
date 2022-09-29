<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Utils;


class HumpUtil
{
    /**
     * key转驼峰
     * @param array $content
     * @return array|mixed|string|string[]|null
     */
    public static function keyToHump(array $content)
    {
        $hump = [];
        if ($content) {
            $hump = preg_replace_callback('/[_]([a-zA-Z])(?=[^"]*?":)/',
                function ($matches) {
                    return strtoupper($matches[1]);
                }, json_encode($content));
            $hump = json_decode($hump, true);
        }
        return $hump;
    }

    /**
     * @param string $key
     * @return string
     */
    public static function key(string $key): string
    {
        $key = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $key);
        return $key;
    }
}