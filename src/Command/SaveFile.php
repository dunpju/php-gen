<?php


namespace Dengpju\PhpGen\Command;


trait SaveFile
{
    /**
     * @param string $file
     * @param string $content
     */
    protected function write(string $file, string $content)
    {
        if (!file_exists($file)) { //文件不存在
            $handle = fopen($file, "w");
            if ($handle) {
                $cont = fwrite($handle, $content);
                if ($cont === false) {
                    echo "Cannot write to a file {$file}" . PHP_EOL;
                } else {
                    echo "{$file} Write to successful" . PHP_EOL;
                }
            } else {
                echo 'Failed to create a file.' . PHP_EOL;
            }
        } else { //文件已经存在
            if (is_writable($file)) {
                $handle = fopen($file, "w");
                $cont = fwrite($handle, $content);
                if ($cont === false) {
                    echo "Cannot write to a file {$file}" . PHP_EOL;
                } else {
                    echo "{$file} Write to successful" . PHP_EOL;
                }
            } else {
                echo 'File unwritable' . PHP_EOL;
            }
        }
    }
}