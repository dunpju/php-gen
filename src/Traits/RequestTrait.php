<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Traits;


use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

trait RequestTrait
{
    #[Inject]
    protected RequestInterface $request;
}