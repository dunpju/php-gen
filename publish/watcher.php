<?php
declare(strict_types=1);

!defined('BASE_PATH') && define('BASE_PATH', __DIR__);

$mywatchPid = `ps -ef |grep 'mywatcher' |grep -v grep |awk '{print $1}'`;
if ($mywatchPid) {
    $mywatchPid = str_replace(PHP_EOL, "", $mywatchPid);
    Swoole\Process::kill((int)$mywatchPid, SIGTERM);
}

$container = [];
$isReload = false;
$resourceId = null;

function scanner(string $dir, array $excludes)
{
    global $container;
    global $isReload;
    $dirFiles = glob($dir . "/*");
    foreach ($dirFiles as $path) {
        if (in_array($path, $excludes)) {
            continue;
        }
        if (is_file($path)) {
            if (isset($container[$path])) {
                $md5File = md5_file($path);
                if (strcmp($container[$path], $md5File) !== 0) {
                    $isReload = true;
                }
                $container[$path] = $md5File;
            } else {
                $container[$path] = md5_file($path);
            }
        } elseif (is_dir($path)) {
            scanner($path, $excludes);
        }
    }
}

function deldir(string $path): bool
{
    if (is_dir($path)) {
        $p = scandir($path);
        if (count($p) > 2) {
            foreach ($p as $val) {
                if ($val != "." && $val != "..") {
                    if (is_dir($path . $val)) {
                        deldir($path . $val . '/');
                    } else {
                        unlink($path . $val);
                    }
                }
            }
        }
    }
    if (is_dir($path)) {
        return rmdir($path);
    }
    return true;
}

$watchProcess = new Swoole\Process(function (Swoole\Process $process) {
    cli_set_process_title("mywatcher");
    $config = require_once BASE_PATH . "/config/autoload/watcher.php";
    $excludes = $config["excludes"];
    $dirs = $config["dirs"];
    $files = $config["files"];

    $pid = `ps -ef |grep 'Master' |grep -v grep |awk '{print $1}'`;
    if ($pid) {
        $pid = str_replace(PHP_EOL, "", $pid);
        Swoole\Process::kill((int)$pid, SIGTERM);
    }

    deldir(BASE_PATH . "/runtime/container/proxy/");

    $descriptorspec = [
        0 => STDIN,
        1 => STDOUT,
        2 => STDERR,
    ];

    global $resourceId;
    global $isReload;

    $resourceId = proc_open("php bin/hyperf.php start", $descriptorspec, $pipes);

    while (true) {

        $isReload = false;
        foreach ($dirs as $dir) {
            scanner($dir, $excludes);
        }
        foreach ($files as $file) {
            scanner($file, $excludes);
        }
        if ($isReload) {
            $status = proc_get_status($resourceId);
            Swoole\Process::kill((int)$status['pid'], SIGTERM);
            proc_close($resourceId);
            deldir(BASE_PATH . "/runtime/container/proxy/");
            $resourceId = proc_open("php bin/hyperf.php start", $descriptorspec, $pipes);
        }
        sleep(3);
    }
});

$watchProcess->start();
