<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Payload\ClassPayload;
use Dengpju\PhpGen\Payload\MethodPayload;
use Dengpju\PhpGen\Utils\DirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

/**
 * php bin/hyperf.php dengpju:config
 * Class RouteCommand
 * @package App\Command
 */
#[Command]
class ConfigCommand extends BaseCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dengpju:config');
    }

    public function configure()
    {
        parent::configure();
        $description = str_pad("Build Config Instance.", self::STR_PAD_LENGTH, " ", STR_PAD_RIGHT);
        $this->setDescription($description . 'php bin/hyperf.php dengpju:config');
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
        ];
    }

    public function handle()
    {
        $this->autoPublish();
        dump(BASE_PATH);
        $componentsDir = BASE_PATH . "/app/Config/Components";
        if (!DirUtil::mkdir($componentsDir)) {
            echo "Failed to create a directory." . PHP_EOL;
            exit(1);
        }
        DirUtil::empty($componentsDir);

        $globs = glob(BASE_PATH . "/config/autoload/*.php");
        foreach ($globs as $file) {
            $this->classContainer = [];
            dump($file);
            $basename = basename($file, ".php");
            dump($basename);
            $ClassPayload = new ClassPayload();
            $ClassPayload->path = $basename;
            $this->classContainer[$basename] = $ClassPayload;
            dump(config($basename));
            $this->each(config($basename), $basename);
            foreach ($this->classContainer as $class) {

            }
            break;
        }
        dump($this->classContainer);
        $this->line("fffff", 'info');
    }

    /**
     * @var ClassPayload[]
     */
    protected array $classContainer = [];

    protected function each(array $config, string $parent)
    {
        foreach ($config as $key => $value) {
            // 方法
            if (isset($this->classContainer[$parent])) {
                $MethodPayload = new MethodPayload();
                $MethodPayload->name = $key;
                $this->classContainer[$parent]->methods[] = $MethodPayload;
            }
            if (is_string($key)) {
                if (is_array($value)) {
                    $hasNumericKey = false;
                    foreach ($value as $k => $v) {
                        if (is_numeric($k)) {
                            $hasNumericKey = true;
                            break;
                        }
                    }
                    if (!$hasNumericKey) {
                        // 类
                        $classPath = $parent . "." . $key;
                        $ClassPayload = new ClassPayload();
                        $ClassPayload->path = $classPath;
                        $this->classContainer[$classPath] = $ClassPayload;
                        $this->each($value, $classPath);
                    } else {
                        echo $parent, " ", $key, " = ", json_encode($value, JSON_UNESCAPED_UNICODE), PHP_EOL;
                    }
                } else {
                    echo $parent, " ", $key, " = ", $value, PHP_EOL;
                }
            } else {
                echo $parent, " {$key} = ", $value, PHP_EOL;
            }
        }
    }
}
