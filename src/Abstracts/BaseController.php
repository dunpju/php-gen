<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Abstracts;


use Dengpju\PhpGen\Constants\ResponseCode;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

abstract class BaseController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    /**
     * @param $data
     * @param string $message
     * @param int $code
     * @param array $headers
     *
     * @return Psr7ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function success($data = null, string $message = '', int $code = ResponseCode::SUCCESS, array $headers = []): Psr7ResponseInterface
    {
        if (!$message) {
            $message = ResponseCode::getMessage(ResponseCode::SUCCESS);
        }
        $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
        list($headers, $json) = $this->resp($data, $message, $code, $headers);
        return $response->json($json)->withHeader('X-Response-Time', time());
    }

    /**
     * @param $data
     * @param string $message
     * @param int $code
     * @param array $headers
     *
     * @return Psr7ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function error($data, string $message = '', int $code = ResponseCode::FAIL, array $headers = []): Psr7ResponseInterface
    {
        if (!$message) {
            $message = ResponseCode::getMessage(ResponseCode::FAIL);
        }
        $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
        list($headers, $json) = $this->resp($data, $message, $code, $headers);
        return $response->json($json)->withAddedHeader('X-Response-Time', time());
    }

    /**
     * @param $content
     * @param string $message
     * @param int $code
     * @param array $headers
     * @return array
     */
    protected function resp($content, string $message, int $code, array $headers): array
    {
        $time = time();
        $headers = array_merge($headers, [
            'X-Response-Time' => $time,
        ]);
        $data = [];
        $data['msg'] = $message;
        $data['data'] = $content;
        $data['code'] = $code;
        $data['timestamp'] = $time;
        return [$headers, $data];
    }

    /**
     * 转驼峰
     * @param array|string $content
     * @return mixed
     */
    protected function hump(array|string $content): mixed
    {
        if (is_string($content)) return $content;
        $hump = [];
        if ($content) {
            $hump = preg_replace_callback('/[_]([a-zA-Z])(?=[^"]*?":)/',
                function ($matches) {
                    return strtoupper($matches[1]);
                }, json_encode($content));
            $hump = json_decode($hump, true);
        }
        return $hump;
    }
}