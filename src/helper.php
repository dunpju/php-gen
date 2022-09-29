<?php
declare(strict_types=1);

use Dengpju\PhpGen\Traits\RuleMessage;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

! defined('BASE_PATH') && define('BASE_PATH', getcwd());

if (!function_exists('validate')) {
    /**
     * @param array $data
     * @param RuleMessage $ruleMessage
     * @return bool|int
     */
    function validate(array $data, RuleMessage $ruleMessage): bool|int
    {
        $validator = make(ValidatorFactoryInterface::class)->make($data, $ruleMessage->rule(), $ruleMessage->message());
        if ($validator->fails()) {
            return (int)$validator->errors()->first();
        }
        return true;
    }
}