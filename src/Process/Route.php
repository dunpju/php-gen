<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Process;


use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "route")]
class Route extends AbstractProcess
{
    /**
     * @var \Swoole\Coroutine\Server|\Swoole\Server
     */
    private \Swoole\Server|\Swoole\Coroutine\Server $server;

    /**
     * @param \Swoole\Coroutine\Server|\Swoole\Server $server
     * @return bool
     */
    public function isEnable($server): bool
    {
        $this->server = $server;
        if (true == config("app_debug")) {
            return true;
        }
        return false;
    }

    public function handle(): void
    {
        go(function () {
            $descriptorspec = [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ];

            proc_open("php bin/hyperf.php dengpju:route server=http", $descriptorspec, $pipes);
        });
        while (true){sleep(2);}
    }
}