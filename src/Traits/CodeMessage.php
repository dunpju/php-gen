<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Traits;

/**
 * Trait CodeMessage
 * @package Dengpju\PhpGen\Traits
 *
 * @method static string getMessage(int $code, $translate = null)
 */
trait CodeMessage
{
    /**
     * @var Code
     */
    protected Code $code;

    /**
     * @param int $value
     * @param string $tips
     * @return static
     */
    public static function Get(int $value, string $tips = ''): self
    {
        $self = new self();
        $message = $self::getMessage($value, $tips);
        $self->code = new Code($value, $message);
        return $self;
    }

    /**
     * @return string
     */
    public function Message(): string
    {
        return $this->code->getMessage();
    }

    /**
     * @return int
     */
    public function Code(): int
    {
        return $this->code->getCode();
    }

}