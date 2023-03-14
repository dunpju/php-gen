<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Payload;


class MethodPayload
{
    public const BOOL = 1;
    public const INT = 2;
    public const STRING = 3;
    public const ARRAY = 4;
    public const OBJECT = 5;

    public string $name = "";
    public int $returnType = 0;
    public string $return = "";
}