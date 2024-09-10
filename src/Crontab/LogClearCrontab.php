<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Crontab;


use Dengpju\PhpGen\Utils\DirUtil;
use Hyperf\Crontab\Annotation\Crontab;

use function Hyperf\Config\config;

/**
 * Class LogClearCrontab
 * @package App\Crontab
 */
// 每天00:00:00执行
//#[Crontab(rule: "0 0 12/1 * *", name: "LogClearCrontab", callback: "execute", memo: "清理日志", enable: "isEnable")]
//#[Crontab(rule: "*/2 * * * * *", name: "LogClear", callback: "execute", memo: "清理日志", enable: "isEnable")]
#[Crontab(rule: "rule", name: "LogClear", callback: "execute", memo: "清理日志", enable: "isEnable")]
class LogClearCrontab
{
    public function execute()
    {
        $path = BASE_PATH . "/runtime/logs";
        if (!DirUtil::mkdir($path)) {
            return;
        }
        $logs = glob($path . "/*");
        foreach ($logs as $log) {
            preg_match_all("/\d+/", $log, $d);
            if ($d[0]) {
                if (strtotime(implode("-", $d[0])) < strtotime((date('Y-m-d', strtotime("-7 day", time()))))) {
                    echo "remove {$log}" . PHP_EOL;
                    unlink($log);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function rule(): string
    {
        return (string)config("gen_crontab.rule");
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return (bool)config("gen_crontab.enable");
    }
}