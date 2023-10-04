<?php

namespace Dengpju\PhpGen\Visitor;

use Hyperf\Utils\Str;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\NodeVisitorAbstract;

class ModelRewriteTableVisitor extends NodeVisitorAbstract
{
    public string $namespace;

    public function __construct(protected string $prefix)
    {
    }


    /**
     * @param Node $node
     * @return Node\Stmt\Namespace_|Node\Stmt\Property|null
     */
    public function enterNode(Node $node): Node\Stmt\Property|Node\Stmt\Namespace_|null
    {
        if ($node instanceof Node\Stmt\Property) {
            if ($node->props[0]->name->toLowerString() === 'table') {
                $attributes = $node->props[0]->default->getAttributes();
                $attributes["rawValue"] = str_replace("'", "", $attributes["rawValue"]);
                $attributes["rawValue"] = Str::replaceFirst($this->prefix, '', $attributes["rawValue"]);
                $node->props[0]->default = new Node\Scalar\String_($attributes["rawValue"], $attributes);
                $node->type = new Node\NullableType(new Identifier('string'));
                return $node;
            }
        } elseif ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = implode("\\", $node->name->parts);
            return $node;
        }
        return null;
    }
}