<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\CamelizeUtil;
use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * php bin/hyperf.php dengpju:entity conn=default model=all
 * php bin/hyperf.php dengpju:entity conn=default model=Address
 * Class EntityCommand
 * @package App\Command
 */
#[Command]
class EntityCommand extends BaseCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:entity');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Entity.", 20, " ", STR_PAD_RIGHT);
        $this->setDescription($description . "php bin/hyperf.php dengpju:entity conn=default model=all Or php bin/hyperf.php dengpju:entity conn=default model=ModelName");
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['conn', InputArgument::REQUIRED, 'Data connection'],
            ['model', InputArgument::REQUIRED, 'Model Class Name,all Generate Full Model Class Entity'],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function handle()
    {
        $conn = $this->input->getArgument('conn');
        $inputModelName = $this->input->getArgument('model');
        $inputModelName = str_replace("model=", "", $inputModelName);

        $databases = config("databases");
        $conns = array_keys($databases);
        $conn = str_replace("conn=", "", $conn);
        if (!in_array($conn, $conns)) {
            echo "Connection:{$conn} No Exist" . PHP_EOL;
            exit(1);
        }

        $this->uses = config("gen.entity.uses");
        $this->inheritance = config("gen.entity.inheritance");
        $this->baseStorePath = config("gen.entity.base_store_path");
        $this->baseNamespace = config("gen.entity.base_namespace");

        $this->combine();

        $connConfig = config("databases.{$conn}");
        $commands = $connConfig['commands'];
        $genModel = $commands['gen:model'];
        $modelPath = $genModel['path'] . "/" . ucfirst(CamelizeUtil::camelize($conn));
        $modelNamespace = str_replace("/", "\\", ucfirst($modelPath));

        $storePath = "{$this->baseStorePath}/" . ucfirst(CamelizeUtil::camelize($conn));
        if (!MkdirUtil::dir($storePath)) {
            echo "Failed to create a directory." . PHP_EOL;
            exit(1);
        }

        $scanPath = BASE_PATH . "/{$modelPath}";
        $phpfiles = glob($scanPath . "/*.php");
        foreach ($phpfiles as $php) {
            require_once($php);
        }

        if ("all" != $inputModelName) {
            $class = $modelNamespace . '\\' . $inputModelName;
            $refClass = new \ReflectionClass($class);
            $classDoc = $refClass->getDocComment();
            $attributes = $this->parseClassDoc($classDoc);
            $fileName = $refClass->getShortName() . "Entity";
            $file = $storePath . "/{$fileName}.php";
            if (!file_exists($file)) {
                $namespace = rtrim($this->baseNamespace, "\\") . "\\" . basename(dirname($file));
                $this->uses = array_filter(array_unique($this->uses));
                $uses = [];
                foreach ($this->uses as $use) {
                    $uses[] = "use {$use}";
                }
                $class = $fileName;
                $inheritance = $this->inheritance;
                $propertys = $this->propertys($attributes);
                $this->write($file, $this->content($namespace, $uses, $class, $inheritance, $propertys));
            }
        } else {
            $classes = get_declared_classes();

            foreach ($classes as $class) {
                if (str_contains($class, $modelNamespace . "\\")) {
                    $refClass = new \ReflectionClass($class);
                    $classDoc = $refClass->getDocComment();
                    $attributes = $this->parseClassDoc($classDoc);
                    $fileName = $refClass->getShortName() . "Entity";
                    $file = $storePath . "/{$fileName}.php";
                    if (!file_exists($file)) {
                        $namespace = rtrim($this->baseNamespace, "\\") . "\\" . basename(dirname($file));
                        $this->uses = array_filter(array_unique($this->uses));
                        $uses = [];
                        foreach ($this->uses as $use) {
                            $uses[] = "use {$use}";
                        }
                        $class = $fileName;
                        $inheritance = $this->inheritance;
                        $propertys = $this->propertys($attributes);
                        $this->write($file, $this->content($namespace, $uses, $class, $inheritance, $propertys));
                    }
                }
            }
        }

        $this->line('finish', 'info');
    }

    /**
     * @param array $propertys
     * @return string
     */
    protected function propertys(array $propertys): string
    {
        $index = 0;
        $collect = [];
        foreach ($propertys as $property => $msg) {
            if (!$index) {
                $collect[] = '/**';
            } else {
                $collect[] = '    /**';
            }
            $collect[] = '     * @Message("' . str_replace("\r", "", str_replace(PHP_EOL, "", end($msg))) . '")';
            $collect[] = '     */';
//            $collect[] = '     public ' . $msg[0] . ' $' . CamelizeUtil::camelize($attribute) . ' = null;';
            $collect[] = '     public $' . CamelizeUtil::camelize($property) . ' = null;';
            $index++;
        }
        return implode(PHP_EOL, $collect);
    }

    /**
     * @param string $namespace
     * @param array $uses
     * @param string $class
     * @param string $inheritance
     * @param array $propertys
     * @return string
     */
    protected function content(string $namespace, array $uses, string $class, string $inheritance, string $propertys): string
    {
        $tpl = file_get_contents(__DIR__ . "/tpl/entity.stub");
        $this->replaceNamespace($tpl, $namespace);
        $uses = implode(";" . PHP_EOL, $uses) . ";";
        $this->replaceUses($tpl, $uses);
        $this->replaceClass($tpl, $class);
        $this->replaceInheritance($tpl, $inheritance);
        $this->replacePropertys($tpl, $propertys);
        return $tpl;
    }

    /**
     * @param string $tpl
     * @param string $propertys
     */
    protected function replacePropertys(string &$tpl, string $propertys)
    {
        $tpl = str_replace(
            ['%PROPERTYS%'],
            [$propertys],
            $tpl
        );
    }
}
