<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Process;


use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;
use function Hyperf\Coroutine\go;

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
        $servers = config("server.servers");
        go(function () use ($servers){
            foreach ($servers as $server) {

                $descriptorspec = [
                    0 => STDIN,
                    1 => STDOUT,
                    2 => STDERR,
                ];

                proc_open(sprintf("php bin/hyperf.php dengpju:route server=%s", $server["name"]), $descriptorspec, $pipes);
            }
        });
        while (true){sleep(2);}
    }
}