<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Annotations;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EnumAttribute
{
    public const Value = "Value";

    public function __construct(private string $name)
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}