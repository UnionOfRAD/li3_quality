<?php

namespace li3_quality\tests\cases\test;

use li3_quality\tests\mocks\test\Testable;

class TestableTest extends \li3_quality\test\Unit {

	public function testParseLines() {
		$testable = new Testable(array(
			'source' => "one\rtwo\nthree\r\nfour",
		));
		$this->assertEqual(4, count($testable->lines()));
	}

	public function testParseDoubleLinesR() {
		$testable = new Testable(array(
			'source' => "one\r\rtwo\nthree\r\nfour",
		));
		$lines = $testable->lines();
		$this->assertEqual(5, count($lines));
		$this->assert(empty($lines[1]));
	}

	public function testParseDoubleLinesN() {
		$testable = new Testable(array(
			'source' => "one\rtwo\n\nthree\r\nfour",
		));
		$lines = $testable->lines();
		$this->assertEqual(5, count($lines));
		$this->assert(empty($lines[2]));
	}

	public function testParseDoubleLinesRN() {
		$testable = new Testable(array(
			'source' => "one\rtwo\nthree\r\n\r\nfour",
		));
		$lines = $testable->lines();
		$this->assertEqual(5, count($lines));
		$this->assert(empty($lines[3]));
	}


	public function testFindNext() {
		$code = <<<EOD
class foo {
	function bar() {

	}
}
EOD;
		$testable = $this->_testable($code);
		$id = $testable->findNext(array(T_FUNCTION), 0);
		$this->assertIdentical(6, $id);
	}

	public function testFindNextUnreachable() {
		$code = <<<EOD
class foo {
}
EOD;
		$testable = $this->_testable($code);
		$id = $testable->findNext(array(T_FUNCTION), 0);
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
		$id = $testable->findPrev(array(T_CLASS), 6);
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
		$id = $testable->findPrev(array(T_CLASS), 6);
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
		$id = $testable->findNext(array(T_FUNCTION), 0); // function bar

		$inClass = $testable->tokenIn(array(T_CLASS), $id);
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
		$id = $testable->findNext(array(T_FUNCTION), 0); // function bar

		$inClass = $testable->tokenIn(array(T_WHILE), $id);
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
		$id = $testable->findNext(array(T_WHILE), 0); // while (false)

		$inClass = $testable->tokenIn(array(T_CLASS), $id);
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
		$id = $testable->findNext(array(T_WHILE), 0); // while (false)

		$inClass = $testable->tokenIn(array(T_WHILE), $id);
		$this->assertIdentical(false, $inClass);
	}

}

?>