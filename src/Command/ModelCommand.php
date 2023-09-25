<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\CamelizeUtil;
use Dengpju\PhpGen\Utils\DirUtil;
use Dengpju\PhpGen\Visitor\ModelRewriteTableVisitor;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Str;
use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * php bin/hyperf.php dengpju:model conn=default table=all
 * Class ModelCommand
 * @package App\Command
 */
#[Command]
class ModelCommand extends BaseCommand
{

    protected ?Lexer $lexer = null;

    protected ?Parser $astParser = null;

    protected ?PrettyPrinterAbstract $printer = null;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:model');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Model.", self::STR_PAD_LENGTH, " ", STR_PAD_RIGHT);
        $this->setDescription($description . 'php bin/hyperf.php dengpju:model table=all --conn=default --prefix=fm_ --path=Default Or php bin/hyperf.php dengpju:model conn=default --table=table-name --prefix=fm_ --path=Default');
        $this->addArgument('table', InputOption::VALUE_REQUIRED, 'Table Name,all Generate Full Table Model');
        $this->addOption('conn', null, InputOption::VALUE_REQUIRED, 'Data connection');
        $this->addOption('prefix', null, InputOption::VALUE_OPTIONAL, 'Table Prefix');
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Output folder, relatively The configuration of gen:model.path');
    }

    public function handle()
    {
        $this->autoPublish();

        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $this->astParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7, $this->lexer);
        $this->printer = new Standard();

        $inputTableName = $this->input->getArgument('table');
        $inputConn = $this->input->getOption('conn') ?? 'default';
        $inputPrefix = $this->input->getOption('prefix') ?? "";
        $inputPath = $this->input->getOption('path') ?? "";

        $inputTableName = str_replace("table=", "", $inputTableName);

        if (!$inputTableName) {
            $this->line('table name can not be empty.', 'info');
            return;
        }
        $conn = $inputConn;
        $databases = config("databases");
        $conns = array_keys($databases);
        if (!in_array($conn, $conns)) {
            $this->line("Connection:{$conn} No Exist.", 'info');
            return;
        }

        $connConfig = config("databases.{$conn}");
        $commands = $connConfig['commands'];
        $genModel = $commands['gen:model'];
        if ($inputPath) {
            $path = $genModel['path'] . "/" . ucfirst(CamelizeUtil::camelize($inputPath));
        } else {
            $path = $genModel['path'] . "/" . ucfirst(CamelizeUtil::camelize($conn));
        }
        if (!DirUtil::mkdir(BASE_PATH . "/{$path}")) {
            $this->line('Failed to create a directory.', 'info');
            exit(1);
        }

        $uses = isset($genModel['uses']) ? $genModel['uses'] : null;
        $inheritance = $genModel['inheritance'];

        $database = $connConfig['database'];
        $prefix = $connConfig['prefix'];
        if ($inputPrefix) {
            $prefix = $inputPrefix;
        }

        if ($inputTableName == "all") {
            if (!$prefix) {
                $q = "show tables";
            } else {
                $q = "show tables WHERE Tables_in_{$database} LIKE '{$prefix}%'";
            }
            $tables = DB::connection($conn)->select($q);
        } else {
            $stdClass = new \stdClass();
            $stdClass->{"Tables_in_{$database}"} = $inputTableName;
            $tables = [
                $stdClass
            ];
        }

        $command = 'gen:model';
        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$database}"};
            if (!preg_match("/\d+$/", $tableName)) {
                $cmd = "php bin/hyperf.php {$command} {$tableName} --pool='{$conn}' --path='{$path}' --inheritance={$inheritance} --with-comments";
                if ($uses) {
                    $cmd .= " --uses='{$uses}'";
                }
                if ($prefix) {
                    $cmd .= " --prefix='{$prefix}'";
                }
                $res = `$cmd`;
                echo $res;
                usleep(500);
                $tableName = Str::replaceFirst($prefix, '', $tableName);
                $class = Str::studly(Str::singular($tableName));
                $this->ast("{$path}/{$class}", $prefix);
            }
        }
        $this->line('finish', 'info');
    }

    /**
     * @param string $path
     * @param string $prefix
     * @return void
     */
    private function ast(string $path, string $prefix)
    {
        $absolutePath = BASE_PATH . "/{$path}.php";
        $oldCode = file_get_contents($absolutePath);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($oldCode);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new ModelRewriteTableVisitor($prefix));
            $ast = $traverser->traverse($ast);
            $context = $this->printer->prettyPrintFile($ast);
            file_put_contents($absolutePath, $context);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }
    }
}
