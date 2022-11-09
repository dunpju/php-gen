<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Constants;


use Dengpju\PhpGen\Annotations\Message;
use Dengpju\PhpGen\Traits\CodeMessage;
use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;


#[Constants]
class ResponseCode extends AbstractConstants
{
    use CodeMessage;

    /**
     * @Message("成功")
     */
    public const SUCCESS = 200;
    /**
     * @Message("失败")
     */
    public const FAIL = 400;
    /**
     * @Message("服务器开小差了")
     */
    public const SERVER_ERROR = 500;
    /**
     * @Message("主键%s错误")
     */
    public const PRIMARY_ID_ERROR = 200000;
    /**
     * @Message("%s不存在")
     */
    public const NOT_EXIST_ERROR = 200001;
}