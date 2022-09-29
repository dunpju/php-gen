<?php
declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Hyperf\Command\Command as HyperfCommand;

abstract class BaseCommand extends HyperfCommand
{
    use SaveFile;

    /**
     * @var string
     */
    protected string $baseStorePath;
    /**
     * @var string
     */
    protected string $baseNamespace;

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