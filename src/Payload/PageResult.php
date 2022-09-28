<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Payload;


use Dengpju\PhpGen\Traits\ArrayAccessTrait;

class PageResult
{
    use ArrayAccessTrait;

    public int $total = 0;
    public int $per_page = 0;
    public int $current_page = 0;
    public int $last_page = 0;
    public array $data = [];

    /**
     * @return array
     */
    public function toArray(): array
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_UNICODE), true);
    }
}