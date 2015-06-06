<?php

namespace li3_quality\tests\cases\test\rules\syntax;

class HasCorrectCommentStyleTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\syntax\HasCorrectCommentStyle';

	public function testInlineCommentsInTestMethods() {
		$code = <<<EOD
class Foo {

	public function testSomething() {
		// This can have an inline comment
		return false;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectInlineCommentsInTestMethods() {
		$code = <<<EOD
class Foo {

	pubilc function foobar() {
		return false;
	}

	// This cannot have an inline comment
	public function bar() {
		return false;
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testStrayLineComment() {
		$code = '// foobar';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testFunctionInlineComments() {
		$code = <<<EOD
function foo() {
	//explain some things
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}
}

?>