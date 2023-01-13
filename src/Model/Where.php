<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Model;


class Where
{
    public function __construct(public string $column, public $operator = null, public $value = null, public $boolean = 'and')
    {
    }
}