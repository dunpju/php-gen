<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Annotations;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ExcMiddlewares extends AbstractAnnotation
{
    /**
     * @var Middleware[]
     */
    public array $middlewares = [];

    /**
     * ExcMiddleware constructor.
     * @param array $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware instanceof Middlewares ? $middleware : new Middleware((string) $middleware);
        }
    }

    /**
     * @param array $targetMiddlewares
     * @return array
     */
    public function excludeMiddlewares(array $targetMiddlewares): array
    {
        $excMiddlewares = $this->middlewares;
        $middlewaresArr = json_decode(json_encode($excMiddlewares, JSON_UNESCAPED_UNICODE), true);
        $excMiddlewaresArr = [];
        if ($middlewaresArr) {
            $excMiddlewaresArr = array_column($middlewaresArr, "middleware");
        }
        if ($excMiddlewaresArr) {
            foreach ($targetMiddlewares as $index => $methodMiddleware) {
                if (in_array($methodMiddleware, $excMiddlewaresArr)) {
                    unset($targetMiddlewares[$index]);
                }
            }
            if ($targetMiddlewares) {
                $targetMiddlewares = array_values($targetMiddlewares);
            }
        }
        return $targetMiddlewares;
    }
}