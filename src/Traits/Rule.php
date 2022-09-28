<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Traits;


class Rule
{
    public function __construct(private string $rules, private int $code)
    {
    }

    /**
     * @return string
     */
    public function getRules(): string
    {
        return $this->rules;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }
}