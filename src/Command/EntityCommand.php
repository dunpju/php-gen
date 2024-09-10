<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\CamelizeUtil;
use Dengpju\PhpGen\Utils\DirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

use function Hyperf\Config\config;

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
        $description = str_pad("Build Entity.", self::STR_PAD_LENGTH, " ", STR_PAD_RIGHT);
        $this->setDescription($description . "php bin/hyperf.php dengpju:entity model=all --conn=default --modelPath=Default Or php bin/hyperf.php dengpju:entity model=ModelName --conn=default --modelPath=Default");
        $this->addArgument('model', InputOption::VALUE_REQUIRED, 'Model Class Name,all Generate Full Model Class Entity');
        $this->addOption('conn', null, InputOption::VALUE_REQUIRED, 'Data connection');
        $this->addOption('modelPath', null, InputOption::VALUE_OPTIONAL, 'store directory of model, relatively the configuration of gen:model.path');
    }

    /**
     * @throws \ReflectionException
     */
    public function handle()
    {
        $this->autoPublish();

        $inputModelName = $this->input->getArgument('model');
        $inputModelName = str_replace("model=", "", $inputModelName);
        if (!$inputModelName) {
            $this->line('model name can not be empty.', 'info');
            return;
        }
        $conn = $this->input->getOption('conn') ?? 'default';
        $databases = config("databases");
        $conns = array_keys($databases);
        if (!in_array($conn, $conns)) {
            $this->line("Connection:{$conn} No Exist", 'info');
            return;
        }
        $inputModelPath = $this->input->getOption('modelPath') ?? "";
        if (!$inputModelPath) {
            $inputModelPath = ucfirst(CamelizeUtil::camelize($conn));
        }

        $this->uses = config("gen.entity.uses");
        $this->inheritance = config("gen.entity.inheritance");
        $this->baseStorePath = config("gen.entity.base_store_path");
        $this->baseNamespace = config("gen.entity.base_namespace");

        $this->combine();

        $connConfig = config("databases.{$conn}");
        $commands = $connConfig['commands'];
        $genModel = $commands['gen:model'];
        $modelPath = $genModel['path'] . "/" . $inputModelPath;
        $modelNamespace = str_replace("/", "\\", ucfirst($modelPath));

        $storePath = "{$this->baseStorePath}/" . $inputModelPath;
        if (!DirUtil::mkdir($storePath)) {
            $this->line('Failed to create a directory.', 'info');
            return;
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
