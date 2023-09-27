<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\CamelizeUtil;
use Dengpju\PhpGen\Utils\DirUtil;
use Dengpju\PhpGen\Visitor\ModelRewriteClassVisitor;
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
 * php bin/hyperf.php dengpju:model table=all --conn=default --prefix=fm_ --path=Default
 * php bin/hyperf.php dengpju:model table=fm_notify --conn=default --prefix=fm_ --path=Default
 * Class ModelCommand
 * @package App\Command
 */
#[Command]
class ModelCommand extends BaseCommand
{

    protected ?Lexer $lexer = null;

    protected ?Parser $astParser = null;

    protected ?PrettyPrinterAbstract $printer = null;

    public string $namespace;

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
            return;
        }

        $uses = isset($genModel['uses']) ? $genModel['uses'] : null;
        $inheritance = $genModel['inheritance'];

        $database = $connConfig['database'];
        $prefix = $connConfig['prefix'];
        if ($inputPrefix) {
            if ($prefix && ($prefix != $inputPrefix)) {
                $this->line('database.prefix It needs to be configured as an empty string.', 'info');
                return;
            }
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

                $beforeClass = Str::studly(Str::singular($tableName));
                $tableName = Str::replaceFirst($inputPrefix, '', $tableName);
                $afterClass = Str::studly(Str::singular($tableName));
                $beforePath = "{$path}/{$beforeClass}";

                $beforeAbsolutePath = BASE_PATH . "/{$beforePath}.php";
                $afterAbsolutePath = BASE_PATH . "/{$path}/{$afterClass}.php";
                if (file_exists($afterAbsolutePath)) {
                    copy($afterAbsolutePath, $beforeAbsolutePath);
                }

                $this->beforeAst($beforeAbsolutePath, $beforeClass, $inputPrefix);

                $res = `$cmd`;

                $this->ast($beforeAbsolutePath, $path, $afterClass, $inputPrefix);
                $this->output->writeln(sprintf('<info>Model %s\%s was created.</info>', $this->namespace, $afterClass));
            }
        }
        $this->line('finish', 'info');
    }

    /**
     * @param string $beforeAbsolutePath
     * @param string $beforeClass
     * @param string $prefix
     * @return void
     */
    private function beforeAst(string $beforeAbsolutePath, string $beforeClass, string $prefix)
    {
        $beforeContext = file_get_contents($beforeAbsolutePath);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($beforeContext);
            $traverser = new NodeTraverser();
            $modelRewriteTableVisitor = new ModelRewriteClassVisitor($beforeClass, $prefix);
            $traverser->addVisitor($modelRewriteTableVisitor);
            $ast = $traverser->traverse($ast);
            $context = $this->printer->prettyPrintFile($ast);
            file_put_contents($beforeAbsolutePath, $context);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }
    }

    /**
     * @param string $beforeAbsolutePath
     * @param string $afterPath
     * @param string $afterClass
     * @param string $prefix
     * @return void
     */
    private function ast(string $beforeAbsolutePath, string $afterPath, string $afterClass, string $prefix)
    {
        $beforeContext = file_get_contents($beforeAbsolutePath);
        $afterAbsolutePath = BASE_PATH . "/{$afterPath}/{$afterClass}.php";
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($beforeContext);
            $traverser = new NodeTraverser();
            $modelRewriteTableVisitor = new ModelRewriteClassVisitor($afterClass, $prefix);
            $traverser->addVisitor($modelRewriteTableVisitor);
            $ast = $traverser->traverse($ast);
            $context = $this->printer->prettyPrintFile($ast);
            file_put_contents($afterAbsolutePath, $context);
            unlink($beforeAbsolutePath);
            $this->namespace = $modelRewriteTableVisitor->namespace;
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }
    }
}
