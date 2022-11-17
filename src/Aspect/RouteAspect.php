<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Aspect;


use Dengpju\PhpGen\Annotations\ExcMiddlewares;
use Dengpju\PhpGen\Annotations\Group;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class RouteAspect extends AbstractAspect
{

    public array $classes = [
        \Hyperf\HttpServer\Router\RouteCollector::class . "::addRoute"
    ];

    protected bool $switch = false;

    public function __construct()
    {
        $this->switch = (bool)config("aop_route.switch");
    }

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     * @throws \Hyperf\Di\Exception\Exception
     * @throws \ReflectionException
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        if ($this->switch) {
            $globalPrefix = (string)config("aop_route.global_prefix");
            $handler = $proceedingJoinPoint->arguments["keys"]["handler"];
            if (is_array($handler) && count($handler) >= 2) {
                $options = $proceedingJoinPoint->arguments["keys"]["options"];
                $targetMiddlewares = $options["middleware"];
                $handler = $proceedingJoinPoint->arguments["keys"]["handler"];
                $refHandlerMethod = new \ReflectionMethod($handler[0], $handler[1]);
                $methodGroup = $refHandlerMethod->getAttributes(Group::class);
                if ($methodGroup) {
                    /**
                     * @var Group $methodGroupInstance
                     */
                    $methodGroupInstance = $methodGroup[0]->newInstance();
                    if ($methodGroupInstance->prefix) {
                        $proceedingJoinPoint->arguments["keys"]["route"] = "/" . trim($methodGroupInstance->prefix, "/") . $proceedingJoinPoint->arguments["keys"]["route"];
                    }
                } else {
                    $refHandlerClass = new \ReflectionClass($handler[0]);
                    $classGroup = $refHandlerClass->getAttributes(Group::class);
                    if ($classGroup) {
                        /**
                         * @var Group $classGroupInstance
                         */
                        $classGroupInstance = $classGroup[0]->newInstance();
                        if ($classGroupInstance->prefix) {
                            $proceedingJoinPoint->arguments["keys"]["route"] = "/" . trim($classGroupInstance->prefix, "/") . $proceedingJoinPoint->arguments["keys"]["route"];
                        }
                    }
                }
                $excMiddlewares = $refHandlerMethod->getAttributes(ExcMiddlewares::class);
                if ($excMiddlewares) {
                    /**
                     * @var ExcMiddlewares $excMiddlewaresInstance
                     */
                    $excMiddlewaresInstance = $excMiddlewares[0]->newInstance();
                    $proceedingJoinPoint->arguments["keys"]["options"]["middleware"] = $excMiddlewaresInstance->excludeMiddlewares($targetMiddlewares);
                }
            }
            if ($globalPrefix) {
                $proceedingJoinPoint->arguments["keys"]["route"] = "/" . trim($globalPrefix, "/") . $proceedingJoinPoint->arguments["keys"]["route"];
            }
        }
        return $proceedingJoinPoint->process();
    }
}