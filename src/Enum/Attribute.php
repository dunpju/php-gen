<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Enum;


use Dengpju\PhpGen\Annotations\EnumAttribute;
use Dengpju\PhpGen\Exception\EnumException;
use ReflectionClass;

trait Attribute
{
    /**
     * @var bool
     */
    private static bool $isInit = false;
    /**
     * @var ReflectionClass
     */
    private static ReflectionClass $selfReflectionClass;
    /**
     * @var array
     */
    private static array $container = [];

    public static function init()
    {
        if (!static::$isInit) {
            static::$selfReflectionClass = new  ReflectionClass(static::class);
            $refClass = static::$selfReflectionClass;
            $enumAttribute = $refClass->getAttributes(EnumAttribute::class);
            if ($enumAttribute) {
                /**
                 * @var EnumAttribute $enumAttributeInstance
                 */
                $enumAttributeInstance = $enumAttribute[0]->newInstance();
                foreach ($refClass->getConstants() as $name => $val) {
                    $attributes = $refClass->getReflectionConstant($name)->getAttributes($enumAttributeInstance->getName());
                    if ($attributes) {
                        $attribute = $attributes[0];
                        static::$container[$val] = $attribute->newInstance();
                    } else {
                        throw new EnumException($enumAttributeInstance->getName());
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
        if (EnumAttribute::Value === $name) {
            if (count($arguments) < 1) {
                throw new EnumException("枚举Pointer参数错误");
            }
            $value = $arguments[0];
            if (!isset(static::$container[$value])) {
                static::init();
            }
            if (!isset(static::$container[$value])) {
                $class = basename(str_replace("\\", "/", static::class));
                throw new EnumException("[{$value}]不存在{$class}枚举中");
            }
            return static::$container[$value];
        }
    }
}