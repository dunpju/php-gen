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
    protected array $uses = [
        "use Dengpju\PhpGen\Annotations\Message",
        "use Hyperf\HttpServer\Annotation\Controller",
        "use Hyperf\HttpServer\Annotation\DeleteMapping",
        "use Hyperf\HttpServer\Annotation\GetMapping",
        "use Hyperf\HttpServer\Annotation\PostMapping",
        "use Hyperf\HttpServer\Annotation\PutMapping"
    ];

    /**
     * @var string
     */
    protected string $inheritance = "BaseController";

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:controller');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Controller.", 20, " ", STR_PAD_RIGHT);
        $this->setDescription($description . 'php bin/hyperf.php controller:command name=test path="Test"');
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
        $name = $this->input->getArgument('name');
        $path = (string)$this->input->getArgument('path');
        $name = str_replace("name=", "", $name);
        if ($path) {
            $path = str_replace("path=", "", $path);
            $storePath = BASE_PATH . "/app/Controller/" . $path;
        } else {
            $storePath = BASE_PATH . "/app/Controller";
        }
        if (!MkdirUtil::dir($storePath)) {
            echo "Failed to create a directory." . PHP_EOL;
            exit();
        }
        $controllerName = ucfirst(str_replace("Controller", "", $name) . "Controller");
        $file = $storePath . "/{$controllerName}.php";
        if (!file_exists($file)) {
            $namespace = "App\\Controller\\" . str_replace("/", "\\", $path);
            $namespace = rtrim($namespace, "\\");
            $uses = $this->uses;
            $serviceNamespace = "App\\Services\\" . str_replace("/", "\\", $path);
            $serviceNamespace = rtrim($serviceNamespace, "\\");

            if (!preg_match("/Controller$/", $storePath)) {
                $uses[] = "use App\Controller\BaseController";
            }
            $class = $controllerName;
            $inheritance = $this->inheritance;
            $serviceName = ucfirst(str_replace("Service", "", $name) . "Service");
            $uses[] = "use {$serviceNamespace}\\{$serviceName}";
            $this->write($file, $this->content($namespace, $uses, $class, $inheritance, $serviceName));
            $validateCmd = "php bin/hyperf.php validate:command name={$name} path={$path}";
            $res = `$validateCmd`;
            $this->line($res, 'info');
            $serviceCmd = "php bin/hyperf.php service:command name={$name} path={$path}";
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
