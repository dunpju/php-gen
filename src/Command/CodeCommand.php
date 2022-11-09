<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Annotations\Message;
use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * php bin/hyperf.php dengpju:route server=http
 * Class RouteCommand
 * @package App\Command
 */
#[Command]
class CodeCommand extends BaseCommand
{
    protected string $ymalFileDirectory;
    protected string $className;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:code');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Code.", 20, " ", STR_PAD_RIGHT);
        $this->setDescription($description . 'php bin/hyperf.php dengpju:code');
        $this->addOption("--reverse", null, null, "From class to generate yaml file");
        $this->addOption("--force", null, null, "Whether to force overwrite");
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::OPTIONAL, 'Code Class Name'],
        ];
    }

    public function handle()
    {
        $this->autoPublish();

        $name = $this->input->getArgument('name');
        if ($name) {
            $name = str_replace("name=", "", $name);
        }

        $reverse = $this->input->getOption("reverse");
        $force = $this->input->getOption("force");

        $this->uses = config("gen.code.uses");
        $this->traits = config("gen.code.traits");
        $this->inheritance = config("gen.code.inheritance");
        $this->baseStorePath = config("gen.code.base_store_path");
        $this->baseNamespace = config("gen.code.base_namespace");
        $this->ymalFileDirectory = config("gen.code.ymal_file_directory");
        $this->className = config("gen.code.class_name");
        if ($name) {
            $this->className = $name;
        }
        if (!$this->className) {
            echo "Class Name Can't be empty." . PHP_EOL;
            exit(1);
        }

        if ($reverse) {
            $files = [];
            $names = [];
            $codes = [];
            $messages = [];
            $prev = 0;
            $currentFileName = "";
            $ref = new \ReflectionClass("{$this->baseNamespace}{$this->className}");
            foreach ($ref->getConstants() as $name => $val) {
                $docComment = $ref->getReflectionConstant($name)->getDocComment();

                preg_match_all("/(?<=(\@Message\()).*?(?=(\)))/", $docComment, $doc);

                $message = strtolower($name) . "错误";
                if ($doc) {
                    if (isset($doc[0]) && isset($doc[0][0]) && !empty($doc[0][0])) {
                        $message = trim($doc[0][0], '"');
                    }
                } else {
                    $messageAnno = $ref->getReflectionConstant($name)->getAttributes(Message::class);
                    if ($messageAnno) {
                        /**
                         * @var Message $messageAnnoInstance
                         */
                        $messageAnnoInstance = $messageAnno[0]->newInstance();
                        $message = $messageAnnoInstance->text;
                    }
                }
                if ($prev) {
                    if (($prev + 1) != $val) {
                        $prev = $val;
                        $currentFileName = $prev;
                    } else {
                        $prev = $val;
                    }
                } else {
                    $prev = $val;
                    $currentFileName = $prev;
                }

                if (!isset($files[$currentFileName])) {
                    $files[$currentFileName][strtolower($name)] = [
                        "code" => $val,
                        "message" => $message
                    ];
                } else {
                    $files[$currentFileName] = array_merge($files[$currentFileName], [
                        strtolower($name) => [
                            "code" => $val,
                            "message" => $message
                        ]
                    ]);
                }

                if (in_array($names, $names)) {
                    echo "Constant {$name} Duplication." . PHP_EOL;
                    exit(1);
                }
                $names[] = $name;
                if (in_array($val, $codes)) {
                    echo "Code {$val} Duplication." . PHP_EOL;
                    exit(1);
                }
                $codes[] = $val;
            }

            if ($files) {
                foreach ($files as $name => $context) {
                    $yaml = Yaml::dump($context);

                    if (!MkdirUtil::dir($this->ymalFileDirectory)) {
                        echo "Failed to create a directory." . PHP_EOL;
                        exit(1);
                    }
                    file_put_contents("{$this->ymalFileDirectory}/{$name}.yaml", $yaml);
                }
            }
        } else {

            $this->combine();

            try {
                $yamlfiles = glob($this->ymalFileDirectory . "/*.yaml");
                if ($yamlfiles) {
                    $contexts = [];
                    foreach ($yamlfiles as $yamlfile) {
                        $basename = basename($yamlfile);
                        $baseCode = str_replace(".yaml", "", $basename);
                        $i = 0;
                        $values = Yaml::parseFile($yamlfile);
                        foreach ($values as $key => $item) {
                            $item["name"] = $key;
                            $i++;
                            if (isset($item["code"])) {
                                $i = $item["code"] - $baseCode;
                                if ($i < 0) {
                                    echo "{$basename} file Code {$item["code"]} Must greater than or equal to {$baseCode}" . PHP_EOL;
                                    exit(1);
                                }
                            }
                            $item["code"] = $baseCode + $i;
                            if (!isset($item["message"])) {
                                $item["message"] = "{$key}错误";
                            }
                            if (isset($contexts[$item["code"]])) {
                                echo "{$basename} file Code {$item["code"]} Duplication." . PHP_EOL;
                                exit(1);
                            }
                            $contexts[$item["code"]] = $item;
                        }
                    }

                    ksort($contexts);

                    $file = $this->baseStorePath . "/{$this->className}.php";
                    $namespace = rtrim($this->baseNamespace, "\\");
                    $this->uses = array_filter(array_unique($this->uses));
                    $uses = [];
                    foreach ($this->uses as $use) {
                        $uses[] = "use {$use}";
                    }
                    $class = $this->className;
                    $inheritance = $this->inheritance;
                    $traits = array_unique($this->traits);
                    $traitsTmp = [];
                    foreach ($traits as $index => $trait) {
                        if (!$index) {
                            $traitsTmp[] = "use {$trait}";
                        } else {
                            $traitsTmp[] = "        {$trait}";
                        }
                    }
                    $traits = $traitsTmp;

                    $consts = [];
                    foreach ($contexts as $context) {
                        $m = $context["message"];
                        $n = strtoupper($context["name"]);
                        $c = $context["code"];
                        $consts[] = "    /**";
                        $consts[] = "     * @Message(\"{$m}\")";
                        $consts[] = "     */";
                        $consts[] = "     public const {$n} = {$c};";
                    }
                    $consts = implode(PHP_EOL, $consts);

                    $isWrite = true;
                    if (file_exists($file) && !$force) {
                        $isWrite = false;
                        fwrite(STDOUT, "[{$file}] Already in existence, Overwrite or not [Y/n]:");
                        $in = fgets(STDIN);
                        if ("Y" == strtoupper(str_replace(PHP_EOL, "", $in))) {
                            $isWrite = true;
                        }
                    }
                    if ($isWrite) {
                        $this->write($file, $this->content($namespace, $uses, $class, $inheritance, $traits, $consts));
                    } else {
                        echo "Not written" . PHP_EOL;
                        exit(1);
                    }
                } else {
                    echo "Not found yaml file" . PHP_EOL;
                    exit(1);
                }
            } catch (ParseException $e) {
                echo "Yaml Parse error: " . $e->getMessage() . PHP_EOL;
                exit(1);
            }
        }

        $this->line('Code finish', 'info');
    }

    /**
     * @param string $namespace
     * @param array $uses
     * @param string $class
     * @param string $inheritance
     * @param array $traits
     * @param string $consts
     * @return string
     */
    protected function content(string $namespace, array $uses, string $class, string $inheritance, array $traits, string $consts): string
    {
        $tpl = file_get_contents(__DIR__ . "/tpl/code.stub");
        $this->replaceNamespace($tpl, $namespace);
        $uses = implode(";" . PHP_EOL, array_filter($uses)) . ";";
        $this->replaceUses($tpl, $uses);
        $this->replaceClass($tpl, $class);
        $this->replaceInheritance($tpl, $inheritance);
        $traits = implode("," . PHP_EOL, array_filter($traits)) . ";";
        $this->replaceTrait($tpl, $traits);
        $this->replaceConst($tpl, $consts);
        return $tpl;
    }

    /**
     * @param string $tpl
     * @param string $const
     */
    protected function replaceConst(string &$tpl, string $const)
    {
        $tpl = str_replace(
            ['%CONST%'],
            [$const],
            $tpl
        );
    }
}
