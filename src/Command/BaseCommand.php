<?php
declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\ConfigProvider;
use Hyperf\Command\Command as HyperfCommand;

abstract class BaseCommand extends HyperfCommand
{
    use SaveFile;

    /**
     * @var array|string[]
     */
    protected array $uses = [];
    /**
     * @var string
     */
    protected string $inheritance = "";
    /**
     * @var string
     */
    protected string $baseStorePath;
    /**
     * @var string
     */
    protected string $baseNamespace;

    protected function autoPublish()
    {
        $ConfigProvider = new ConfigProvider();
        $provider = $ConfigProvider();
        $publishes = $provider["publish"];
        $isPublish = true;
        foreach ($publishes as $publish) {
            if (!file_exists($publish["destination"])) {
                $isPublish = false;
            }
        }
        if (!$isPublish) {
            $composer = file_get_contents(dirname(__DIR__, 2) . "/composer.json");
            $stdClass = json_decode($composer);
            $res = `php bin/hyperf.php vendor:publish {$stdClass->name}`;
            echo $res;
            $this->error('Please re-execute the command');
            exit(1);
        }
    }

    protected function combine()
    {
        if (str_contains($this->inheritance, "\\")) {
            $this->uses[] = $this->inheritance;
            $this->uses = array_unique($this->uses);
            $this->inheritance = basename(str_replace("\\", "/", $this->inheritance));
        }
    }

    /**
     * @param string $classDoc
     * @return array
     */
    protected function parseClassDoc(string $classDoc): array
    {
        preg_match_all('/(?<=(\@property\\s)).*?(?=(\n))/', $classDoc, $doc);
        $attributes = [];
        if ($doc[0]) {
            $doc = $doc[0];
            array_walk($doc, function ($v) use (&$attributes) {
                $tmp = explode(" ", $v);
                if (!isset($tmp[1])) {
                    return;
                }
                $tmp[1] = str_replace("$", "", $tmp[1]);
                $attributes[$tmp[1]] = $tmp;
            });
        }
        return $attributes;
    }

    /**
     * @param string $tpl
     * @param string $namespace
     */
    protected function replaceNamespace(string &$tpl, string $namespace)
    {
        $tpl = str_replace(
            ['%NAMESPACE%'],
            [$namespace],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $uses
     */
    protected function replaceUses(string &$tpl, string $uses)
    {
        $tpl = str_replace(
            ['%USES%'],
            [$uses],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $class
     */
    protected function replaceClass(string &$tpl, string $class)
    {
        $tpl = str_replace(
            ['%CLASS%'],
            [$class],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $inheritance
     */
    protected function replaceInheritance(string &$tpl, string $inheritance)
    {
        $tpl = str_replace(
            ['%INHERITANCE%'],
            [$inheritance],
            $tpl
        );
    }
}