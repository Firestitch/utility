<?php

namespace Utility\View\MapModel;

use Exception;
use Framework\Util\FileUtil;
use PhpParser\{Lexer, NodeTraverser, NodeVisitor, Parser, PrettyPrinter};
use PhpParser\Node\Stmt\Class_;


class ModelParser {

  private $_file;

  public function __construct($file) {
    $lexer = new Lexer\Emulative([
      'usedAttributes' => [
        'comments',
        'startLine',
        'endLine',
        'startTokenPos',
        'endTokenPos',
      ],
    ]);

    $parser = new Parser\Php7($lexer);

    $traverser = new NodeTraverser();
    $traverser->addVisitor(new NodeVisitor\CloningVisitor());

    $this->_file = $file;
    $this->_oldStmts = $parser->parse(FileUtil::get($file));
    $this->_oldTokens = $lexer->getTokens();
    $this->_newStmts = $traverser->traverse($this->_oldStmts);
  }

  public function getMethod($name) {
    return $this->getClass()
      ->getMethod($name);
  }

  public function getClass(): Class_ {
    $namespace = value($this->_newStmts, 0);

    foreach ($namespace->stmts as &$stmt) {
      if ($stmt instanceof Class_) {
        return $stmt;
      }
    }

    throw new Exception("Failed to locate class");
  }

  public function saveCode() {
    FileUtil::put($this->_file, $this->getCode());
  }

  public function getCode() {
    $printer = new PrettyPrinter\Standard(["shortArraySyntax" => true]);

    return $printer->printFormatPreserving($this->_newStmts, $this->_oldStmts, $this->_oldTokens);
  }
}
