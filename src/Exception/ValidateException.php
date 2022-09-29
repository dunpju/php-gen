<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Exception;


/**
 * Class ValidateException
 * @package Dengpju\PhpGen\Exception
 */
class ValidateException extends BaseException
{
    /**
     * ValidateException constructor.
     * @param $code
     * @param string $message
     * @param \Throwable|null $previous
     */
    public function __construct(int $code, string $message = '', \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}