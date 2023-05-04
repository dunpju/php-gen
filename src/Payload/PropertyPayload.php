<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Payload;


class PropertyPayload
{
    public function __construct(public string $type, public string $name)
    {
    }
}