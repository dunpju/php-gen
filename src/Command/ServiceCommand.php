<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * php bin/hyperf.php service:command name=name path=path
 * Class ServiceCommand
 * @package App\Command
 */
#[Command]
class ServiceCommand extends BaseCommand
{
    /**
     * @var array|string[]
     */
    protected array $uses = [
        "use App\Exception\BusinessException",
        "use App\Exception\ValidateException",
        "use App\Traits\RequestTrait",
        "use Hyperf\DbConnection\Db"
    ];

    protected array $traits = [
        "use RequestTrait",
    ];

    /**
     * @var string
     */
    protected string $inheritance = "";

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('service:command');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Build Service');
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'Service Name'],
            ['path', InputArgument::OPTIONAL, 'Relative Services Directory'],
        ];
    }

    public function handle()
    {
        $name = $this->input->getArgument('name');
        $path = (string)$this->input->getArgument('path');
        $name = str_replace("name=", "", $name);
        if ($path) {
            $path = str_replace("path=", "", $path);
        }
        if ($path) {
            $storePath = BASE_PATH . "/app/Services/" . $path;
        } else {
            $storePath = BASE_PATH . "/app/Services";
        }
        if (!MkdirUtil::dir($storePath)) {
            echo "Failed to create a directory." . PHP_EOL;
            exit();
        }

        $serviceName = ucfirst(str_replace("Service", "", $name) . "Service");
        $file = $storePath . "/{$serviceName}.php";
        if (!file_exists($file)) {
            $namespace = "App\\Services\\" . str_replace("/", "\\", $path);
            $namespace = rtrim($namespace, "\\");
            $validateTrait = "App\\Validates\\" . str_replace("/", "\\", $path);
            $validateTrait = rtrim($validateTrait, "\\");
            $uses = $this->uses;
            $trait = ucfirst(str_replace("Validate", "", $name) . "Validate");
            $uses[] = "use " . $validateTrait . "\\" . $trait;
            $class = $serviceName;
            $traits = $this->traits;
            $traits[] = "        {$trait}";
            $this->write($file, $this->content($namespace, $uses, $class, $traits));
        }

        $this->line('Service finish', 'info');
    }

    /**
     * @param string $namespace
     * @param array $uses
     * @param string $class
     * @param array $traits
     * @return string
     */
    protected function content(string $namespace, array $uses, string $class, array $traits): string
    {
        $tpl = file_get_contents(__DIR__ . "/tpl/service.stub");
        $this->replaceNamespace($tpl, $namespace);
        $uses = implode(";" . PHP_EOL, array_filter($uses)) . ";";
        $this->replaceUses($tpl, $uses);
        $this->replaceClass($tpl, $class);
        $traits = implode("," . PHP_EOL, array_filter($traits)) . ";";
        $this->replaceTrait($tpl, $traits);
        return $tpl;
    }

    /**
     * @param string $tpl
     * @param string $trait
     */
    protected function replaceTrait(string &$tpl, string $trait)
    {
        $tpl = str_replace(
            ['%TRAIT%'],
            [$trait],
            $tpl
        );
    }
}
