<?php
declare(strict_types=1);


namespace Dengpju\PhpGen;


use Swoole\Coroutine\Channel;

/**
 * $ctx = new Context();
 * $ret = $ctx->withTimeout(3, function () {
 *     sleep(3);
 *     return 1;
 * })->done();
 * dump($ret);
 * Class Context
 * @package Dengpju\PhpGen
 */
class Context
{
    /**
     * @var Channel
     */
    protected Channel $channel;
    /**
     * @var int|float
     */
    protected int|float $timeout;

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->channel = new Channel(1);
    }

    /**
     * @param int|float $timeout
     * @param callable $callable
     * @return $this
     */
    public function withTimeout(int|float $timeout, callable $callable): static
    {
        $this->timeout = $timeout;
        $this->do($callable);
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    protected function do(callable $callable): static
    {
        \Swoole\Coroutine::create(function () use ($callable) {
            $beginTime = time();
            $over = new Channel(1);
            $begin = false;
            while (true) {
                if (!$begin) {
                    \Swoole\Coroutine::create(function () use ($over, $callable) {
                        $this->channel->push($callable());
                        $over->push(true);
                    });
                }
                $begin = true;
                if ((time() - $beginTime) > $this->timeout || $over->pop(0.1)) {
                    break;
                }
            }
        });
        return $this;
    }

    /**
     * @return mixed
     */
    public function done(): mixed
    {
        return $this->channel->pop($this->timeout);
    }
}