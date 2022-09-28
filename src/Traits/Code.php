<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Traits;


class Code
{
    /**
     * @var int
     */
    protected int $code;
    /**
     * @var string
     */
    protected string $message;

    /**
     * Code constructor.
     * @param int $code
     * @param string $message
     */
    public function __construct(int $code, string $message)
    {
        $this->code = $code;
        $this->message = $message;
    }


    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}