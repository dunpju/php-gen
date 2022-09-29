<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Traits\RequestTrait;
use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * php bin/hyperf.php dengpju:service name=name path=path
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
        RequestTrait::class,
        Db::class
    ];
    /**
     * @var array|string[]
     */
    protected array $traits = [
        RequestTrait::class,
    ];
    /**
     * @var string
     */
    protected string $validateBaseNamespace;
    /**
     * @var string
     */
    protected string $validateException;
    /**
     * @var string
     */
    protected string $businessException;
    /**
     * @var string
     */
    protected string $inheritance = "";

    /**
     * ServiceCommand constructor.
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:service');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Service.", 20, " ", STR_PAD_RIGHT);
        $this->setDescription($description . 'php bin/hyperf.php dengpju:service name=name path=path');
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'Service Name'],
            ['path', InputArgument::OPTIONAL, 'Relative Base Store Path Directory'],
        ];
    }

    public function handle()
    {
        $this->uses = array_merge($this->uses, config("gen.service.uses"));
        $this->traits = array_merge($this->traits, config("gen.service.traits"));
        $this->baseStorePath = config("gen.service.base_store_path");
        $this->baseNamespace = config("gen.service.base_namespace");
        $this->validateBaseNamespace = config("gen.validate.base_namespace");
        $this->validateException = config("gen.service.validate_exception");
        $this->businessException = config("gen.service.business_exception");

        $traits = [];
        foreach ($this->traits as $trait) {
            if (str_contains($trait, "\\")) {
                $this->uses[] = $trait;
                $traits[] = basename(str_replace("\\", "/", $trait));
            }
        }

        $validateException = $this->validateException;
        $businessException = $this->businessException;
        if (str_contains($validateException, "\\")) {
            $this->uses[] = $validateException;
            $validateException = basename(str_replace("\\", "/", $validateException));
        }
        if (str_contains($businessException, "\\")) {
            $this->uses[] = $businessException;
            $businessException = basename(str_replace("\\", "/", $businessException));
        }

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
            exit(1);
        }

        $serviceName = ucfirst(str_replace("Service", "", $name) . "Service");
        $file = $storePath . "/{$serviceName}.php";
        if (!file_exists($file)) {
            $namespace = $this->baseNamespace . str_replace("/", "\\", $path);
            $namespace = rtrim($namespace, "\\");
            $validateTrait = $this->validateBaseNamespace . str_replace("/", "\\", $path);
            $validateTrait = rtrim($validateTrait, "\\");

            $this->uses = array_unique($this->uses);
            $uses = [];
            foreach ($this->uses as $use) {
                $uses[] = "use {$use}";
            }
            $currentTrait = ucfirst(str_replace("Validate", "", $name) . "Validate");
            $uses[] = "use " . $validateTrait . "\\" . $currentTrait;
            $class = $serviceName;
            $traits[] = $currentTrait;
            $traits = array_unique($traits);
            $traitsTmp = [];
            foreach ($traits as $index => $trait) {
                if (!$index) {
                    $traitsTmp[] = "use {$trait}";
                } else {
                    $traitsTmp[] = "        {$trait}";
                }
            }
            $traits = $traitsTmp;
            $this->write($file, $this->content($namespace, $uses, $class, $traits, $validateException, $businessException));
        }

        $this->line('Service finish', 'info');
    }

    /**
     * @param string $namespace
     * @param array $uses
     * @param string $class
     * @param array $traits
     * @param string $validateException
     * @param string $businessException
     * @return string
     */
    protected function content(string $namespace, array $uses, string $class, array $traits, string $validateException, string $businessException): string
    {
        $tpl = file_get_contents(__DIR__ . "/tpl/service.stub");
        $this->replaceNamespace($tpl, $namespace);
        $uses = implode(";" . PHP_EOL, array_filter($uses)) . ";";
        $this->replaceUses($tpl, $uses);
        $this->replaceClass($tpl, $class);
        $traits = implode("," . PHP_EOL, array_filter($traits)) . ";";
        $this->replaceTrait($tpl, $traits);
        $this->replaceValidateException($tpl, $validateException);
        $this->replaceBusinessException($tpl, $businessException);
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

    /**
     * @param string $tpl
     * @param string $validateException
     */
    protected function replaceValidateException(string &$tpl, string $validateException)
    {
        $tpl = str_replace(
            ['%VALIDATE_EXCEPTION%'],
            [$validateException],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $businessException
     */
    protected function replaceBusinessException(string &$tpl, string $businessException)
    {
        $tpl = str_replace(
            ['%BUSINESS_EXCEPTION%'],
            [$businessException],
            $tpl
        );
    }
}
