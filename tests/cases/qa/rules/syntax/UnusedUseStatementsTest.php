<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class UnusedUseStatementsTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\UnusedUseStatements';

	public function testBasicStaticAndInstanceClasses() {
		$code = <<<EOD
use foo;
use foo\bar;
new bar();
foo::baz();
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testAliasClass() {
		$code = <<<EOD
use foo as bar;
new bar();
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testExtending() {
		$code = <<<EOD
use foo\bar\baz\MockTestable as Testable;
use Baz;
class Foo implements Baz extends Testable {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCaseInsensitive() {
		$code = <<<EOD
use Baz;
class Foo implements baz {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testSimpleUnused() {
		$code = <<<EOD
use Baz;
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testUnusedAlias() {
		$code = <<<EOD
use foo\bar\baz\MockTestable as Testable;
class Foo {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testUseStatementsInHeredoc() {
		$code = <<<EOD
<<<EOT
use foo\bar\baz\MockTestable as Testable;
EOT;

EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testOverlappingNames() {
		$code = <<<EOD
use foo;
use foobar;
new foo();
new foobar();
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	/**
	 * IDE Concern #869
	 */
	public function testIgnoreUnusedSplInterfaces() {
		$code = <<<EOD
use ArrayAccess;
use Closure;
use Iterator;
use IteratorAggregate;
use Serializable;
use Traversable;
EOD;
		$this->assertRulePass($code, $this->rule);
	}
}

?>