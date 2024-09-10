<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Model;
use Hyperf\DbConnection\Model\Model;

class WhereUpdate
{

    /**
     * @var Where[]
     */
    public array $wheres = [];

    public function __construct(protected Model $model)
    {
    }

    /**
     * @param string $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where(string $column, $operator = null, $value = null, string $boolean = 'and'): static
    {
        $this->wheres[] = new Where($column, $operator, $value, $boolean);

        return $this;
    }
}