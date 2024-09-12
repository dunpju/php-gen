<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Enum;


use Dengpju\PhpGen\Exception\EnumException;
use ReflectionClass;

trait Attribute
{
    /**
     * @var bool
     */
    private static bool $isInit = false;
    /**
     * @var string
     */
    private static string $staticClass = "";
    /**
     * @var ReflectionClass
     */
    private static ReflectionClass $selfReflectionClass;
    /**
     * @var array
     */
    private static array $container = [];

    protected static function init(): void
    {
        if (!static::$isInit || self::$staticClass != static::class) {
            self::$staticClass = static::class;
            static::$selfReflectionClass = new  ReflectionClass(static::class);
            $refClass = static::$selfReflectionClass;

            foreach ($refClass->getConstants() as $name => $val) {
                $attributes = $refClass->getReflectionConstant($name)->getAttributes();
                foreach ($attributes as $attribute) {
                    $attrInstance = $attribute->newInstance();
                    if ($attrInstance instanceof EnumAttributeInterface) {
                        static::$container[static::class][$name] = $attrInstance;
                    }
                }
            }

            static::$isInit = true;
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (!isset(static::$container[static::class])) {
            static::init();
        }
        if (isset(static::$container[static::class][$name])) {
            return static::$container[static::class][$name];
        }
        return static::$name(...$arguments);
    }
}