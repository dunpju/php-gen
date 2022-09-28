<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * php bin/hyperf.php validate:command name=name path=path
 * Class ValidateCommand
 * @package App\Command
 */
#[Command]
class ValidateCommand extends BaseCommand
{
    /**
     * @var array|string[]
     */
    protected array $uses = [
        "use App\Constants\ResponseCode",
        "use App\Traits\RuleMessage"
    ];

    /**
     * @var string
     */
    protected string $baseStorePath;
    /**
     * @var string
     */
    protected string $baseNamespace;

    public function __construct(protected ContainerInterface $container)
    {
        $this->uses = config("gen.validate.uses");
        $this->baseStorePath = config("gen.validate.base_store_path");
        $this->baseNamespace = config("gen.validate.base_namespace");
        parent::__construct('validate:command');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Build Validate');
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'Validate Trait Name'],
            ['path', InputArgument::OPTIONAL, 'Relative Validates Directory'],
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
            $storePath = $this->baseStorePath . $path;
        } else {
            $storePath = $this->baseStorePath;
        }
        if (!MkdirUtil::dir($storePath)) {
            echo "Failed to create a directory." . PHP_EOL;
            exit();
        }
        $validateName = ucfirst(str_replace("Validate", "", $name) . "Validate");
        $file = $storePath . "/{$validateName}.php";
        if (!file_exists($file)) {
            $namespace = $this->baseNamespace . str_replace("/", "\\", $path);
            $namespace = rtrim($namespace, "\\");
            $uses = [];
            foreach ($this->uses as $use) {
                $uses[] = "use {$use}";
            }
            $trait = $validateName;
            $this->write($file, $this->content($namespace, $uses, $trait));
        }

        $this->line('Validate finish', 'info');
    }

    /**
     * @param string $namespace
     * @param array $uses
     * @param string $class
     * @param array $traits
     * @return string
     */
    protected function content(string $namespace, array $uses, string $trait): string
    {
        $tpl = file_get_contents(__DIR__ . "/tpl/validate.stub");
        $this->replaceNamespace($tpl, $namespace);
        $uses = implode(";" . PHP_EOL, array_filter($uses)) . ";";
        $this->replaceUses($tpl, $uses);
        $this->replaceTrait($tpl, $trait);
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
