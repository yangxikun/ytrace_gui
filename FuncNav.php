<?php

use PhpParser\Error;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

class FuncNav extends NodeVisitorAbstract {
    protected $popupList = [];
    protected $namespace;
    protected $class;
    public function enterNode(Node $node) {

        if ($node instanceof Namespace_) {
            $this->namespace = $node->name;
            return;
        }

        if ($node instanceof Class_) {
            $this->class = empty($this->namespace) ? $node->name
                : $this->namespace . '\\' . $node->name;
            return;
        }

        if ($node instanceof Function_) {
            $params = [];
            foreach ($node->params as $param) {
                if ($param->byRef) {
                    $params[] = '&' . $param->name;
                } else {
                    $params[] = $param->name;
                }
            }
            $this->popupList[] = [
                'type' => 'func',
                'name' => $node->name,
                'params' => implode(", ", $params),
                'line' => $node->getLine()
            ];
            return;
        }

        if ($node instanceof ClassMethod) {
            $params = [];
            foreach ($node->params as $param) {
                if ($param->byRef) {
                    $params[] = '&' . $param->name;
                } else {
                    $params[] = $param->name;
                }
            }
            $this->popupList[] = [
                'type' => 'method',
                'class' => $this->class,
                'name' => $node->name,
                'params' => implode(", ", $params),
                'line' => $node->getLine()
            ];
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = '';
            return;
        }

        if ($node instanceof Class_) {
            $this->class = '';
        }
    }

    public function getPopupList()
    {
        return $this->popupList;
    }
};

function get_funcNav()
{
    $popupList = [];
    $filename = $_GET['file'];
    if (file_exists($filename)) {
        $code = file_get_contents($filename);
        if (!empty($code)) {
            $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
            try {
                $ast = $parser->parse($code);
            } catch (Error $error) {
                header('HTTP/1.0 500 Internal Server Error');
                echo "Parse error: {$error->getMessage()}";
                return;
            }
            $funcNav = new FuncNav();
            $traverser = new NodeTraverser();
            $traverser->addVisitor($funcNav);
            $traverser->traverse($ast);
            $popupList = $funcNav->getPopupList();
        }
    }
    header('content-type: application/json');
    echo json_encode($popupList);
}
