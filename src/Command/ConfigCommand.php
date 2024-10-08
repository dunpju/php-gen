<?php

declare(strict_types=1);

namespace Dengpju\PhpGen\Command;

use Dengpju\PhpGen\Payload\ClassPayload;
use Dengpju\PhpGen\Payload\PropertyPayload;
use Dengpju\PhpGen\Utils\DirUtil;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

use function Hyperf\Config\config;

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
        $properties = [];
        foreach ($config as $key => $value) {
            if (is_string($key)) {
                if (is_array($value)) {
                    $hasNumericKey = false;
                    foreach ($value as $k => $v) {
                        if (is_numeric($k)) {
                            $hasNumericKey = true;
                            break;
                        } elseif (is_string($k)) {
                            $properties[] = new PropertyPayload(gettype($v), $k);
                        }
                    }
                    if (!$hasNumericKey) {
                        // 类
                        $classPath = $parent . "." . $key;
                        $ClassPayload = new ClassPayload();
                        $ClassPayload->path = $classPath;
                        $ClassPayload->properties = $properties;
                        $this->classContainer[$classPath] = $ClassPayload;
                        $this->each($value, $classPath);
                    }
                }
            }
        }
    }
}
