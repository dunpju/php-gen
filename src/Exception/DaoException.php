<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Exception;

/**
 * Class DaoException
 * @package Dengpju\PhpGen\Exception
 */
class DaoException extends BaseException
{
    /**
     * DaoException constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, int $code, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}