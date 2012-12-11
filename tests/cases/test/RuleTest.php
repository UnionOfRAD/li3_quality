<?php

namespace li3_quality\tests\cases\test;

use lithium\analysis\Parser;

class RuleTest extends \li3_quality\test\Unit {

	public $mockRule = 'li3_quality\tests\mocks\test\Rule';

	public function testFindNext() {
		$code = <<<EOD
class foo {
	function bar() {

	}
}
EOD;
		$testable = $this->_testable($code);
		$rule = $this->_rule($this->mockRule);
		$tokens = $testable->tokens();
		$id = $rule->findNext($tokens, array(T_FUNCTION), 0);
		$this->assertIdentical(6, $id);
	}

	public function testFindNextUnreachable() {
		$code = <<<EOD
class foo {
}
EOD;
		$testable = $this->_testable($code);
		$rule = $this->_rule($this->mockRule);
		$tokens = $testable->tokens();
		$id = $rule->findNext($tokens, array(T_FUNCTION), 0);
		$this->assertIdentical(false, $id);
	}

	public function testFindPrev() {
		$code = <<<EOD
class foo {
	function bar() {

	}
}
EOD;
		$testable = $this->_testable($code);
		$rule = $this->_rule($this->mockRule);
		$tokens = $testable->tokens();
		$id = $rule->findPrev($tokens, array(T_CLASS), 6);
		$this->assertIdentical(0, $id);
	}

	public function testFindPrevUnreachable() {
		$code = <<<EOD
function foo {
	function bar() {

	}
}
EOD;
		$testable = $this->_testable($code);
		$rule = $this->_rule($this->mockRule);
		$tokens = $testable->tokens();
		$id = $rule->findPrev($tokens, array(T_CLASS), 6);
		$this->assertIdentical(false, $id);
	}

	public function testTokenInSimple() {
		$code = <<<EOD
class foo {
	function bar() {

	}
}
EOD;
		$testable = $this->_testable($code);
		$rule = $this->_rule($this->mockRule);
		$tokens = $testable->tokens();
		$id = $rule->findNext($tokens, array(T_FUNCTION), 0); // function bar

		$inClass = $rule->tokenIn($tokens, array(T_CLASS), $id);
		$this->assertIdentical(true, $inClass);
	}

	public function testTokenInSimpleFalse() {
		$code = <<<EOD
class foo {
	function bar() {

	}
}
EOD;
		$testable = $this->_testable($code);
		$rule = $this->_rule($this->mockRule);
		$tokens = $testable->tokens();
		$id = $rule->findNext($tokens, array(T_FUNCTION), 0); // function bar

		$inClass = $rule->tokenIn($tokens, array(T_WHILE), $id);
		$this->assertIdentical(false, $inClass);
	}

	public function testTokenInComplex() {
		$code = <<<EOD
class foo {
	function bar() {
		\$foo = "}}}";
		while (false) {

		}
	}
}
EOD;
		$testable = $this->_testable($code);
		$rule = $this->_rule($this->mockRule);
		$tokens = $testable->tokens();
		$id = $rule->findNext($tokens, array(T_WHILE), 0); // while (false)

		$inClass = $rule->tokenIn($tokens, array(T_CLASS), $id);
		$this->assertIdentical(true, $inClass);
	}

	public function testTokenInComplexFalse() {
		$code = <<<EOD
class foo {
	function bar() {
		\$foo = "}}}";
		while (false) {

		}
	}
}
EOD;
		$testable = $this->_testable($code);
		$rule = $this->_rule($this->mockRule);
		$tokens = $testable->tokens();
		$id = $rule->findNext($tokens, array(T_WHILE), 0); // while (false)

		$inClass = $rule->tokenIn($tokens, array(T_WHILE), $id);
		$this->assertIdentical(false, $inClass);
	}

}

?>