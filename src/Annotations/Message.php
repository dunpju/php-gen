<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Annotations;

use Attribute;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"ALL"})
 */
#[Attribute(Attribute::TARGET_ALL)]
class Message
{
    public function __construct(public string $text="")
    {
    }

    /**
     * @param string $docComment
     * @return string
     */
    public static function parse(string $docComment): string
    {
        preg_match_all("/(?<=(\@Message\(\")).*?(?=(\"\)))/", $docComment, $doc);
        if ($doc) {
            if (isset($doc[0]) && isset($doc[0][0]) && !empty($doc[0][0])) {
                return trim($doc[0][0], '"');
            }
        }
        return "";
    }
}