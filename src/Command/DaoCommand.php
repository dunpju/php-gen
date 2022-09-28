<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Utils\CamelizeUtil;
use Dengpju\PhpGen\Utils\MkdirUtil;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * php bin/hyperf.php dao:command conn=default model=all
 * Class DaoCommand
 * @package App\Command
 */
#[Command]
class DaoCommand extends BaseCommand
{
    protected const DELETE_TIME = "deleteTime";

    /**
     * @var array|string[]
     */
    protected array $uses = [
        "use App\Constants\ResponseCode",
        "use App\Dao\BaseDao",
        "use App\Exception\DaoException"
    ];

    /**
     * @var string
     */
    protected string $inheritance = "BaseDao";

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:dao');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Dao.", 20, " ", STR_PAD_RIGHT);
        $this->setDescription($description . 'php bin/hyperf.php dao:command conn=default model=all Or php bin/hyperf.php dao:command conn=default model=ModelName');
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['conn', InputArgument::REQUIRED, 'Data connection'],
            ['model', InputArgument::REQUIRED, 'Model Class Name,all Generate Full Model Class Entity'],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function handle()
    {
        $conn = $this->input->getArgument('conn');
        $inputModelName = $this->input->getArgument('model');
        $inputModelName = str_replace("model=", "", $inputModelName);

        $databases = config("databases");
        $conns = array_keys($databases);
        $conn = str_replace("conn=", "", $conn);
        if (!in_array($conn, $conns)) {
            echo "Connection:{$conn} No Exist" . PHP_EOL;
            exit();
        }
        $connConfig = config("databases.{$conn}");
        $commands = $connConfig['commands'];
        $prefix = $connConfig['prefix'];
        $genModel = $commands['gen:model'];
        $modelPath = $genModel['path'];
        $modelNamespace = str_replace("/", "\\", ucfirst($modelPath));
        $scanPath = BASE_PATH . "/{$modelPath}";
        $basename = basename($scanPath);

        $storePath = BASE_PATH . "/app/Dao/" . ucfirst(CamelizeUtil::camelize($conn));
        if (!MkdirUtil::dir($storePath)) {
            echo "Failed to create a directory." . PHP_EOL;
            exit();
        }

        $phpfiles = glob($scanPath . "/*.php");
        foreach ($phpfiles as $php) {
            require_once($php);
        }

        if ("all" != $inputModelName) {
            $class = $modelNamespace . '\\' . $inputModelName;
            $refClass = new \ReflectionClass($class);
            $classDoc = $refClass->getDocComment();
            $attributes = $this->parseClassDoc($classDoc);
            $fileName = $refClass->getShortName() . "Dao";
            $file = $storePath . "/{$fileName}.php";
            if (!file_exists($file)) {
                $namespace = "App\\Dao\\" . basename(dirname($file));
                $uses = $this->uses;
                $uses[] = "use " . $refClass->getName();
                $uses[] = "use " . str_replace("\\Model\\", "\\Entity\\", $refClass->getName()) . "Entity";
                $class = $fileName;
                $inheritance = $this->inheritance;
                $model = $refClass->getShortName();
                $entity = $refClass->getShortName() . "Entity";
                /**
                 * @var Model $newInstance
                 */
                $newInstance = $refClass->newInstance();
                $primaryKey = CamelizeUtil::camelize($newInstance->getKeyName());
                $ucPrimaryKey = ucfirst(CamelizeUtil::camelize($newInstance->getKeyName()));
                $fieldsMapping = $this->fieldsMapping($primaryKey, $attributes);
                $this->write($file, $this->content($namespace, $uses, $class,
                    $inheritance, $model, $entity,
                    $primaryKey, $ucPrimaryKey, $fieldsMapping));
            }
        } else {
            $classes = get_declared_classes();

            foreach ($classes as $class) {
                if (str_contains($class, $modelNamespace . "\\")) {
                    $refClass = new \ReflectionClass($class);
                    $classDoc = $refClass->getDocComment();
                    $attributes = $this->parseClassDoc($classDoc);
                    $fileName = $refClass->getShortName() . "Dao";
                    $file = $storePath . "/{$fileName}.php";
                    if (!file_exists($file)) {
                        $namespace = "App\\Dao\\" . basename(dirname($file));
                        $uses = $this->uses;
                        $uses[] = "use " . $refClass->getName();
                        $uses[] = "use " . str_replace("\\Model\\", "\\Entity\\", $refClass->getName()) . "Entity";
                        $class = $fileName;
                        $inheritance = $this->inheritance;
                        $model = $refClass->getShortName();
                        $entity = $refClass->getShortName() . "Entity";
                        /**
                         * @var Model $newInstance
                         */
                        $newInstance = $refClass->newInstance();
                        $primaryKey = CamelizeUtil::camelize($newInstance->getKeyName());
                        $ucPrimaryKey = ucfirst(CamelizeUtil::camelize($newInstance->getKeyName()));
                        $fieldsMapping = $this->fieldsMapping($primaryKey, $attributes);
                        $this->write($file, $this->content($namespace, $uses, $class,
                            $inheritance, $model, $entity,
                            $primaryKey, $ucPrimaryKey, $fieldsMapping));
                    }
                }
            }
        }
        $this->line('finish', 'info');
    }

    /**
     * @param string $primaryKey
     * @param array $attributes
     * @return string
     */
    protected function fieldsMapping(string $primaryKey, array $attributes): string
    {
        $res = [];
        foreach ($attributes as $k => $v) {
            $humpAttribute = CamelizeUtil::camelize($k);
            if ($primaryKey != $humpAttribute && self::DELETE_TIME != $humpAttribute) {
                if (count($res)) {
                    $res[] = '            $this->model->' . $k . ' = $entity->' . $humpAttribute . ';';
                } else {
                    $res[] = '$this->model->' . $k . ' = $entity->' . $humpAttribute . ';';
                }
            }
        }
        return implode(PHP_EOL, $res);
    }

    /**
     * @param string $namespace
     * @param array $uses
     * @param string $class
     * @param string $inheritance
     * @param string $model
     * @param string $entity
     * @param string $primaryKey
     * @param string $ucPrimaryKey
     * @param string $fieldsMapping
     * @return string
     */
    protected function content(string $namespace, array $uses, string $class,
                               string $inheritance, string $model, string $entity,
                               string $primaryKey, string $ucPrimaryKey, string $fieldsMapping): string
    {
        $tpl = file_get_contents(__DIR__ . "/tpl/dao.stub");
        $this->replaceNamespace($tpl, $namespace);
        $uses = implode(";" . PHP_EOL, array_filter($uses)) . ";";
        $this->replaceUses($tpl, $uses);
        $this->replaceClass($tpl, $class);
        $this->replaceInheritance($tpl, $inheritance);
        $this->replaceModel($tpl, $model);
        $this->replaceEntity($tpl, $entity);
        $this->replacePrimaryKey($tpl, $primaryKey);
        $this->replaceUcPrimaryKey($tpl, $ucPrimaryKey);
        $this->replaceFieldsMapping($tpl, $fieldsMapping);
        return $tpl;
    }

    /**
     * @param string $tpl
     * @param string $model
     */
    public function replaceModel(string &$tpl, string $model)
    {
        $tpl = str_replace(
            ['%MODEL%'],
            [$model],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $entity
     */
    public function replaceEntity(string &$tpl, string $entity)
    {
        $tpl = str_replace(
            ['%ENTITY%'],
            [$entity],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $primaryKey
     */
    public function replacePrimaryKey(string &$tpl, string $primaryKey)
    {
        $tpl = str_replace(
            ['%PRIMARY_KEY%'],
            [$primaryKey],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $ucPrimaryKey
     */
    public function replaceUcPrimaryKey(string &$tpl, string $ucPrimaryKey)
    {
        $tpl = str_replace(
            ['%UC_PRIMARY_KEY%'],
            [$ucPrimaryKey],
            $tpl
        );
    }

    /**
     * @param string $tpl
     * @param string $fieldsMapping
     */
    public function replaceFieldsMapping(string &$tpl, string $fieldsMapping)
    {
        $tpl = str_replace(
            ['%FIELDS_MAPPING%'],
            [$fieldsMapping],
            $tpl
        );
    }
}
