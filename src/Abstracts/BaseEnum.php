<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Abstracts;


use Dengpju\PhpGen\Annotations\Message;
use Dengpju\PhpGen\Exception\EnumException;

abstract class BaseEnum
{
    /**
     * @param null $value
     * @param bool $strict
     * @return null
     * @throws EnumException
     */
    public static function inspect($value = null, bool $strict = true)
    {
        $constants = self::getEnums();
        if (!in_array($value, $constants, $strict)) {
            $class = basename(str_replace("\\", "/", static::class));
            throw new EnumException("[{$value}]不存在{$class}枚举中");
        }
        return $value;
    }

    /**
     * 枚举数组
     * @return array
     */
    public static function getEnums(): array
    {
        $objClass = new \ReflectionClass(static::class);
        return $objClass->getConstants();
    }

    /**
     * @param string $glue
     * @return string
     */
    public static function implode(string $glue = ","): string
    {
        return implode($glue, self::getEnums());
    }

    /**
     * 范围
     * @var array
     */
    protected static array $range = [];

    /**
     * @param string $key
     * @param string $title
     * @return array
     * @throws \ReflectionException
     */
    public static function select(string $key = "id", string $title = "title"): array
    {
        $res = [];
        foreach (self::list() as $k => $value) {
            if (static::$range) { // 在范围内
                if (in_array($k, static::$range)) {
                    $tmp[$key] = $k;
                    $tmp[$title] = $value;
                    $res[] = $tmp;
                }
            } else {
                $tmp[$key] = $k;
                $tmp[$title] = $value;
                $res[] = $tmp;
            }
        }
        return $res;
    }

    /**
     * @param $value
     * @return string|null
     * @throws EnumException
     */
    public static function getMessage($value): ?string
    {
        $refClass = new \ReflectionClass(static::class);
        foreach (self::getEnums() as $name => $val) {
            if ($value == $val) {
                $docComment = $refClass->getReflectionConstant($name)->getDocComment();
                preg_match_all("/(?<=(\@Message\()).*?(?=(\)))/", $docComment, $doc);
                if ($doc) {
                    return trim($doc[0][0], '"');
                }
            }
        }
        $class = $refClass->getShortName();
        throw new EnumException("[{$value}]未匹配到{$class}枚举@Message");
    }

    /**
     * @return array
     */
    public static function list(): array
    {
        $enums = [];
        $refClass = new \ReflectionClass(static::class);
        foreach (self::getEnums() as $name => $val) {
            $docComment = $refClass->getReflectionConstant($name)->getDocComment();
            if ($doc = Message::parse($docComment)) {
                $enums[$val] = $doc;
            } else {
                $enums[$val] = "未知";
            }
        }
        return $enums;
    }
}