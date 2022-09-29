<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\CamelizeUtil;
use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * php bin/hyperf.php dengpju:enum conn=default name=yes_or_no flag='是否:no-1-否,yes-2-是'
 * Class EnumCommand
 * @package App\Command
 */
#[Command]
class EnumCommand extends BaseCommand
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
        parent::__construct('dengpju:enum');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Enum.", 20, " ", STR_PAD_RIGHT);
        $this->setDescription($description . "php bin/hyperf.php dengpju:enum conn=default name=yes_or_no flag='是否:yes-1-是,no-2-否'");
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['conn', InputArgument::REQUIRED, 'Data connection'],
            ['name', InputArgument::REQUIRED, 'Enum Name'],
            ['flag', InputArgument::REQUIRED, 'Enum Flag'],
        ];
    }

    public function handle()
    {
        $conn = trim($this->input->getArgument('conn'));
        $name = trim($this->input->getArgument('name'));
        $flag = trim($this->input->getArgument('flag'));
        $databases = config("databases");
        $conns = array_keys($databases);
        $conn = str_replace("conn=", "", $conn);
        if (!in_array($conn, $conns)) {
            echo "Connection:{$conn} No Exist" . PHP_EOL;
            exit(1);
        }

        $this->uses = config("gen.enum.uses");
        $this->inheritance = config("gen.enum.inheritance");
        $this->baseStorePath = config("gen.enum.base_store_path");
        $this->baseNamespace = config("gen.enum.base_namespace");

        $storePath = "{$this->baseStorePath}/" . ucfirst(CamelizeUtil::camelize($conn));
        if (!MkdirUtil::dir($storePath)) {
            echo "Failed to create a directory." . PHP_EOL;
            exit(1);
        }
        if (str_contains($this->inheritance, "\\")) {
            $this->uses[] = $this->inheritance;
            $this->uses = array_unique($this->uses);
            $this->inheritance = basename(str_replace("\\", "/", $this->inheritance));
        }

        $name = str_replace("name=", "", $name);
        $name = ucfirst(CamelizeUtil::camelize($name));
        $fileName = "Enum" . $name;
        $file = $storePath . "/{$fileName}.php";

        if (!file_exists($file)) {
            $namespace = $this->baseNamespace . basename(dirname($file));
            $uses = [];
            foreach ($this->uses as $use) {
                $uses[] = "use {$use}";
            }

            $flag = str_replace("flag=", "", $flag);
            $flags = explode(":", $flag);
            $classDoc = $flags[0];
            $class = $fileName;
            $inheritance = $this->inheritance;
            $flags = explode(",", $flags[1]);
            $consts = [];
            foreach ($flags as $f) {
                $exp = explode("-", $f);
                if (count($exp) > 3) {
                    $consts[] = [$exp[0], "-" . $exp[2], $exp[3]];
                } else {
                    $consts[] = explode("-", $f);
                }
            }
            $consts = $this->consts($consts);

            $this->write($file, $this->content($namespace, $uses, $classDoc, $class, $inheritance, $consts));
        }
        $this->line('finish', 'info');
    }

    /**
     * @param array $consts
     * @return string
     */
    protected function consts(array $consts): string
    {
        $propertys = [];
        foreach ($consts as $flag) {
            if (!count($propertys)) {
                $propertys[] = '/**
     * @Message("' . $flag[2] . '")
     */';
            } else {
                $propertys[] = '    /**
     * @Message("' . $flag[2] . '")
     */';
            }

            $propertys[] = '    const ' . strtoupper($flag[0]) . ' = ' . $flag[1] . ';';
        }
        return implode(PHP_EOL, $propertys);
    }

    /**
     * @param string $namespace
     * @param array $uses
     * @param string $classDoc
     * @param string $class
     * @param string $inheritance
     * @param string $consts
     * @return string
     */
    protected function content(string $namespace, array $uses, string $classDoc, string $class,
                               string $inheritance, string $consts): string
    {
        $tpl = file_get_contents(__DIR__ . "/tpl/enum.stub");
        $this->replaceNamespace($tpl, $namespace);
        $uses = implode(";" . PHP_EOL, array_filter($uses)) . ";";
        $this->replaceUses($tpl, $uses);
        $this->replaceClassDoc($tpl, $classDoc);
        $this->replaceClass($tpl, $class);
        $this->replaceInheritance($tpl, $inheritance);
        $this->replaceConsts($tpl, $consts);
        return $tpl;
    }

    /**
     * @param string $tpl
     * @param string $classDoc
     */
    protected function replaceClassDoc(string &$tpl, string $classDoc)
    {
        $tpl = str_replace(
            ['%CLASS_DOC%'],
            [$classDoc],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $class
     */
    protected function replaceConsts(string &$tpl, string $consts)
    {
        $tpl = str_replace(
            ['%CONSTS%'],
            [$consts],
            $tpl
        );
    }
}
