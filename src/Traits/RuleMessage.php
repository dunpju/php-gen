<?php
declare(strict_types=1);


namespace Dengpju\PhpGen\Traits;

class RuleMessage
{
    private array $rule = [];
    private array $message = [];

    /**
     * ValidatorRule constructor.
     * @param array $rules
     * @param array $messages
     */
    public function __construct(array $rules, array $messages = [])
    {
        $rulesTmp = [];
        $messageTmp = [];
        foreach ($rules as $item => $value) {
            if ($value instanceof Rule) {
                $rule = $value->getRules();
                $rulesTmp[$item] = $rule;
                $rule = explode("|", $rule);
                foreach ($rule as $r) {
                    $r = explode(":", $r);
                    $messageTmp["{$item}.{$r[0]}"] = $value->getCode();
                }
            } else {
                $rulesTmp[$item] = $value;
            }
        }
        $rules = $rulesTmp;
        $messages = array_merge($messages, $messageTmp);
        $this->rule = $rules;
        array_walk($messages, function (&$v) {
            $v = (string)$v;
        });
        $this->message = $messages;
    }

    /**
     * @return array
     */
    public function rule(): array
    {
        return $this->rule;
    }

    /**
     * @return array
     */
    public function message(): array
    {
        return $this->message;
    }
}