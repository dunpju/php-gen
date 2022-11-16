<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Annotations;


use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Group extends AbstractAnnotation
{
    /**
     * Group constructor.
     * @param string $prefix
     */
    public function __construct(public string $prefix)
    {
    }
}