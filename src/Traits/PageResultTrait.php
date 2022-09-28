<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Traits;


use Dengpju\PhpGen\Payload\PageResult;
use Hyperf\Contract\LengthAwarePaginatorInterface;

trait PageResultTrait
{
    /**
     * @param LengthAwarePaginatorInterface $lengthAwarePaginator
     * @return PageResult
     */
    public function Page(LengthAwarePaginatorInterface $lengthAwarePaginator): PageResult
    {
        $result = new PageResult();
        $result->total = $lengthAwarePaginator->total();
        $result->per_page = $lengthAwarePaginator->perPage();
        $result->current_page = $lengthAwarePaginator->currentPage();
        $result->last_page = $lengthAwarePaginator->lastPage();
        $result->data = $lengthAwarePaginator->items();
        return $result;
    }
}