<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Abstracts;


use Hyperf\DbConnection\Model\Model;

abstract class BaseModel extends Model
{
    public const CREATED_AT = 'create_time';
    public const UPDATED_AT = 'update_time';
    protected array $guarded = [];
}