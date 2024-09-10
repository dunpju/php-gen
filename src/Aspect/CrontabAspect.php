<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Aspect;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Listener\CrontabRegisterListener;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\ReflectionManager;

#[Aspect]
class CrontabAspect extends AbstractAspect
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public array $classes = [
        CrontabRegisterListener::class . "::buildCrontabByAnnotation"
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws \Hyperf\Di\Exception\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        $Parser = new \Hyperf\Crontab\Parser();
        foreach ($proceedingJoinPoint->arguments as &$argument) {
            if (isset($argument["annotation"])) {
                /**
                 * @var \Hyperf\Crontab\Annotation\Crontab $annotation
                 */
                $annotation = $argument["annotation"];
                if (!$Parser->isValid($annotation->rule)) {
                    $className = reset($annotation->callback);
                    $method = $annotation->rule;
                    try {
                        $reflectionClass = ReflectionManager::reflectClass($className);
                        if ($reflectionClass->hasMethod($method)) {
                            $reflectionMethod = $reflectionClass->getMethod($method);

                            if ($reflectionMethod->isPublic()) {
                                if ($reflectionMethod->isStatic()) {
                                    $annotation->rule = $className::$method();
                                    continue;
                                }

                                $container = ApplicationContext::getContainer();
                                if ($container->has($className)) {
                                    $annotation->rule = $container->get($className)->{$method}();
                                }
                            }
                        } else {
                            $this->logger->error('Crontab rule method does not exist.');
                        }

                    } catch (\ReflectionException $e) {
                        $this->logger->error('Resolve crontab rule failed, skip register.' . $e);
                    }
                }
            }
        }

        return $proceedingJoinPoint->process();
    }
}