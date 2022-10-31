<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * php bin/hyperf.php controller:command name=test path="Test"
 * Class ControllerCommand
 * @package App\Command
 */
#[Command]
class ControllerCommand extends BaseCommand
{
    /**
     * @var array|string[]
     */
    protected array $uses = [];

    /**
     * @var string
     */
    protected string $inheritance = "";

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:controller');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Controller.", 20, " ", STR_PAD_RIGHT);
        $this->setDescription($description . 'php bin/hyperf.php dengpju:controller name=test path="Test"');
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'Controller Name'],
            ['path', InputArgument::OPTIONAL, 'Relative Controller Directory'],
        ];
    }

    public function handle()
    {
        $this->autoPublish();

        $name = $this->input->getArgument('name');
        $path = (string)$this->input->getArgument('path');
        $name = str_replace("name=", "", $name);

        $this->uses = config("gen.controller.uses");
        $this->inheritance = config("gen.controller.inheritance");
        $this->baseStorePath = config("gen.controller.base_store_path");
        $this->baseNamespace = config("gen.controller.base_namespace");
        $serviceBaseNamespace = config("gen.service.base_namespace");

        $this->combine();

        if ($path) {
            $path = str_replace("path=", "", $path);
        }
        if ($path) {
            $path = str_replace("\\", "/", $path);
            $storePath = rtrim($this->baseStorePath, "/") . "/" . $path;
        } else {
            $storePath = $this->baseStorePath;
        }
        if (!MkdirUtil::dir($storePath)) {
            echo "Failed to create a directory." . PHP_EOL;
            exit(1);
        }
        $controllerName = ucfirst(str_replace("Controller", "", $name) . "Controller");
        $file = $storePath . "/{$controllerName}.php";
        if (!file_exists($file)) {
            if ($path) {
                $namespace = rtrim($this->baseNamespace, "\\") . "\\" . $path;
            } else {
                $namespace = rtrim($this->baseNamespace, "\\");
            }
            $namespace = rtrim($namespace, "\\");
            $namespace = str_replace("/", "\\", $namespace);
            $serviceNamespace = $serviceBaseNamespace . str_replace("/", "\\", $path);
            $serviceNamespace = rtrim($serviceNamespace, "\\");

            foreach ($this->uses as $index => $use) {
                $expUse = array_filter(explode("\\", $use));
                $expBaseNamespace = array_filter(explode("\\", $this->baseNamespace));
                if (count($expUse) == count($expBaseNamespace) + 1) {
                    array_pop($expUse);
                    if (implode("\\", $expUse) == implode("\\", $expBaseNamespace)) {
                        unset($this->uses[$index]);
                    }
                }
            }

            $class = $controllerName;
            $inheritance = $this->inheritance;
            $serviceName = ucfirst(str_replace("Service", "", $name) . "Service");
            $this->uses[] = "{$serviceNamespace}\\{$serviceName}";
            $this->uses = array_filter(array_unique($this->uses));
            $uses = [];
            foreach ($this->uses as $use) {
                $uses[] = "use {$use}";
            }
            $this->write($file, $this->content($namespace, $uses, $class, $inheritance, $serviceName));
            $validateCmd = "php bin/hyperf.php dengpju:validate name={$name} path={$path}";
            $res = `$validateCmd`;
            $this->line($res, 'info');
            $serviceCmd = "php bin/hyperf.php dengpju:service name={$name} path={$path}";
            $res = `$serviceCmd`;
            $this->line($res, 'info');
        }
        $this->line('finish', 'info');
    }

    /**
     * @param string $namespace
     * @param array $uses
     * @param string $class
     * @param string $inheritance
     * @param string $service
     * @return string
     */
    protected function content(string $namespace, array $uses, string $class, string $inheritance, string $service): string
    {
        $tpl = file_get_contents(__DIR__ . "/tpl/controller.stub");
        $this->replaceNamespace($tpl, $namespace);
        $uses = implode(";" . PHP_EOL, array_filter($uses)) . ";";
        $this->replaceUses($tpl, $uses);
        $this->replaceClass($tpl, $class);
        $this->replaceInheritance($tpl, $inheritance);
        $this->replaceService($tpl, $service);
        return $tpl;
    }

    /**
     * @param string $tpl
     * @param string $service
     */
    protected function replaceService(string &$tpl, string $service)
    {
        $tpl = str_replace(
            ['%SERVICE%'],
            [$service],
            $tpl
        );
    }
}
