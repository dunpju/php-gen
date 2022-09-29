<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * php bin/hyperf.php dengpju:model conn=default table=all
 * Class ModelCommand
 * @package App\Command
 */
#[Command]
class ModelCommand extends BaseCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:model');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Model.", 20, " ", STR_PAD_RIGHT);
        $this->setDescription($description . 'php bin/hyperf.php dengpju:model conn=default table=all Or php bin/hyperf.php dengpju:model conn=default table=TableName');
        $this->addOption('conn', 'c', InputOption::VALUE_REQUIRED, 'Data connection');
        $this->addOption('table', 't', InputOption::VALUE_REQUIRED, 'Table Name,all Generate Full Table Model');
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['conn', InputArgument::REQUIRED, 'Data connection,Default Value default'],
            ['table', InputArgument::REQUIRED, 'Table Name,all Generate Full Table Model'],
        ];
    }

    public function handle()
    {
        $inputTableName = $this->input->getArgument('table');
        $conn = $this->input->getArgument('conn') ?? 'default';
        $inputTableName = str_replace("table=", "", $inputTableName);

        $databases = config("databases");
        $conns = array_keys($databases);
        $conn = str_replace("conn=", "", $conn);
        if (!in_array($conn, $conns)) {
            echo "Connection:{$conn} No Exist" . PHP_EOL;
            exit();
        }

        $connConfig = config("databases.{$conn}");
        $commands = $connConfig['commands'];
        $genModel = $commands['gen:model'];
        $path = $genModel['path'];
        if (!MkdirUtil::dir(BASE_PATH . "/{$path}")) {
            echo "Failed to create a directory." . PHP_EOL;
            exit();
        }

        $uses = $genModel['uses'];
        $inheritance = $genModel['inheritance'];

        $database = $connConfig['database'];
        $prefix = $connConfig['prefix'];

        if ($inputTableName == "all") {
            $tables = DB::connection($conn)->select('show tables');
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
            if (!preg_match("/\d+/", $tableName)) {
                $cmd = "php bin/hyperf.php {$command} {$tableName} --pool='{$conn}' --path='{$path}' --uses='{$uses}' --inheritance={$inheritance} --with-comments --force-casts";
                //echo $cmd . PHP_EOL;
                $res = `$cmd`;
                echo $res . PHP_EOL;
            }
        }
        $this->line('finish', 'info');
    }
}
