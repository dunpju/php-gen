<?php
declare(strict_types=1);

use Dengpju\PhpGen\Traits\RuleMessage;
use Hyperf\Context\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\HttpServer\Contract\RequestInterface;

use function Hyperf\Support\make;


if (!function_exists('validate')) {
    /**
     * @param array $data
     * @param RuleMessage $ruleMessage
     * @return bool|int
     */
    function validate(array $data, RuleMessage $ruleMessage): bool|int
    {
        $validator = make(ValidatorFactoryInterface::class)->make($data, $ruleMessage->rule(), $ruleMessage->message());
        if ($validator->fails()) {
            return (int)$validator->errors()->first();
        }
        return true;
    }
}

if (!function_exists('input')) {
    /**
     * @param string $key
     * @param null $default
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function input(string $key, $default = null): mixed
    {
        $container = ApplicationContext::getContainer();
        if (!$container->has(RequestInterface::class)) {
            throw new \RuntimeException('RequestInterface is missing in container.');
        }
        return $container->get(RequestInterface::class)->input($key, $default);
    }
}