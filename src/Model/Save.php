<?php
declare(strict_types=1);

namespace Dengpju\PhpGen\Model;


use Dengpju\PhpGen\Exception\ModelException;

/**
 * @method array attributesToArray()
 * @method string getPrimaryKey()
 * @method boolean originalIsEquivalent($key, $value)
 * @method \Hyperf\Database\Model\Builder newQuery()
 *
 * Trait Save
 * @package Dengpju\PhpGen\Model
 */
trait Save
{
    protected bool $isWhereUpdate = false;
    protected WhereUpdate $WhereUpdate;

    /**
     * @param bool $is
     * @return WhereUpdate
     */
    public function whereUpdate(bool $is = true): WhereUpdate
    {
        $this->isWhereUpdate = $is;
        $this->WhereUpdate = new WhereUpdate($this);
        return $this->WhereUpdate;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if ($this->isWhereUpdate) {
            $this->isWhereUpdate = false;
            if (!$this->WhereUpdate->wheres) {
                throw new ModelException("Condition updating, wheres cannot be empty");
            }
            $wheres = [];
            $values = [];
            foreach ($this->attributesToArray() as $key => $value) {
                if ($key == $this->getPrimaryKey()) {
                    $wheres = array_merge([new Where($key, "=", $value)], $this->WhereUpdate->wheres);
                }
                if (!$this->originalIsEquivalent($key, $value)) {
                    $values[$key] = $value;
                }
            }
            if ($wheres) {
                $newQuery = $this->newQuery();
                /**
                 * @var Where $where
                 */
                foreach ($wheres as $where) {
                    $newQuery->where($where->column, $where->operator, $where->value, $where->boolean);
                }
                return (boolean)$newQuery->update($values);
            }
            return false;
        } else {
            return parent::save($options);
        }
    }
}