<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Payload;


class ClassPayload
{
    public string $path = "";
    /**
     * @var array PropertyPayload[]
     */
    public array $properties = [];
}