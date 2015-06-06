<?php

namespace li3_quality\tests\cases\test;

use li3_quality\tests\mocks\test\MockTestable as Testable;
use li3_quality\test\Testable as RealTestable;
use li3_quality\analysis\Parser;

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
		$testable = $this->_testable(array(
			'source' => $code
		));
		$id = $testable->findNext(array(T_FUNCTION), 0);
		$this->assertIdentical(6, $id);
	}

	public function testFindNextUnreachable() {
		$code = <<<EOD
class foo {
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
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
		$testable = $this->_testable(array(
			'source' => $code
		));
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
		$testable = $this->_testable(array(
			'source' => $code
		));
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
		$testable = $this->_testable(array(
			'source' => $code
		));
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
		$testable = $this->_testable(array(
			'source' => $code
		));
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
		$testable = $this->_testable(array(
			'source' => $code
		));
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
		$testable = $this->_testable(array(
			'source' => $code
		));
		$id = $testable->findNext(array(T_WHILE), 0); // while (false)

		$inClass = $testable->tokenIn(array(T_WHILE), $id);
		$this->assertIdentical(false, $inClass);
	}

	public function testFindNextWithArray() {
		$code = <<<EOD
class foo {
	private function bar() {
	}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();
		$visibilityTokens = array(T_PUBLIC, T_PROTECTED, T_PRIVATE);
		$id = $testable->findNext(array(T_FUNCTION), 0); // function bar
		$modifiers = Parser::modifiers($id, $tokens);

		$visibility = $testable->findNext($visibilityTokens, $modifiers);
		$this->assertIdentical(T_PRIVATE, $tokens[$visibility]['id']);
	}

	public function testFindNextContentWithArray() {
		$code = <<<EOD
class foo {
	private function bar() {
	}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();
		$contentToFind = array('public', 'protected', 'private');
		$id = $testable->findNext(array(T_FUNCTION), 0); // function bar
		$modifiers = Parser::modifiers($id, $tokens);

		$visibility = $testable->findNextContent($contentToFind, $modifiers);
		$this->assertIdentical('private', $tokens[$visibility]['content']);
	}

	public function testFindNextContentBasic() {
		$code = <<<EOD
class foo {
	private function bar() {
	}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();

		$visibility = $testable->findNextContent(array('private'), 0);
		$this->assertIdentical('private', $tokens[$visibility]['content']);
	}

	public function testFindNextContentBasicUnreachable() {
		$code = <<<EOD
class foo {
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();

		$visibility = $testable->findNextContent(array('private'), 0);
		$this->assertIdentical(false, $visibility);
	}

	public function testFindPrevWithArray() {
		$code = <<<EOD
class foo {
	protected \$var = array();
	private function bar() {
	}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();
		$tokensToFind = array(T_FUNCTION, T_VARIABLE);
		$id = $testable->findNext(array(T_CLASS), 0); // class foo
		$children = $tokens[$id]['children'];

		$visibility = $testable->findPrev($tokensToFind, $children);
		$this->assertIdentical(T_FUNCTION, $tokens[$visibility]['id']);
	}

	public function testFindPrevContentWithArray() {
		$code = <<<EOD
class foo {
	protected \$var = array();
	private function bar() {
	}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();
		$contentToFind = array('$var', 'function');
		$id = $testable->findNext(array(T_CLASS), 0); // class foo
		$children = $tokens[$id]['children'];

		$foundId = $testable->findPrevContent($contentToFind, $children);
		$this->assertIdentical('function', $tokens[$foundId]['content']);
	}

	public function testFindPrevContentBasic() {
		$code = <<<EOD
class foo {
	function bar() {}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();
		$start = count($tokens) - 1;

		$foundId = $testable->findPrevContent(array('function'), $start);
		$this->assertIdentical('function', $tokens[$foundId]['content']);
	}

	public function testFindPrevContentBasicUnreachable() {
		$code = <<<EOD
class foo {
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();
		$start = count($tokens) - 1;

		$foundId = $testable->findPrevContent(array('function', $start));
		$this->assertIdentical(false, $foundId);
	}

	public function testFindAll() {
		$code = <<<EOD
class foo {
	public function bar() {}
	private function bar() {}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));

		$ids = $testable->findAll(array(T_FUNCTION), 0);
		$this->assertIdentical(2, count($ids));
	}

	public function testFindAllWithArray() {
		$code = <<<EOD
class foo {
	public \$foo = 'baz';
	protected function bar() {}
	private function bar() {}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code
		));
		$tokens = $testable->tokens();
		$classId = $testable->findNext(array(T_CLASS), 0);
		$children = $tokens[$classId]['children'];

		$ids = $testable->findAllContent(array('function', '$foo'), $children);
		$this->assertIdentical(3, count($ids));
	}

	public function testSource() {
		$code = <<<EOD
class foo {
	public \$foo = 'baz';
	protected function bar() {}
	private function bar() {}
}
EOD;
		$testable = new Testable(array(
			'source' => $code,
		));
		$source = $testable->source();

		$this->assertIdentical($code, $source);
	}

	public function testConfigBasic() {
		$testable = new Testable(array(
			'source' => '<?php echo "foobar"; ?>',
		));

		$expected = array(
			'source' => '<?php echo "foobar"; ?>',
			'wrap' => false,
		);
		$result = $testable->config();

		$this->assertIdentical($expected, $result);
	}

	public function testConfigSpecificKey() {
		$config = array(
			'source' => 'echo "foobar";',
		);
		$testable = new Testable($config);

		$expected = $config['source'];
		$config = $testable->config('source');

		$this->assertIdentical($expected, $config);
	}

	public function testConstructorAutosetWrap() {
		$config = array(
			'path' => __FILE__,
		);
		$testable = new RealTestable($config);

		$expected = false;
		$result = $testable->config('wrap');

		$this->assertIdentical($expected, $result);
	}

	public function testLineIsPHP() {
		$code = <<<EOD
<?php
class foo {
	function bar() {

	}
}
EOD;
		$testable = $this->_testable(array(
			'source' => $code,
			'wrap' => false,
		));

		$this->assertIdentical(true, $testable->isPHP(1));
		$this->assertIdentical(true, $testable->isPHP(2));
	}

	public function testLineIsNotPHP() {
		$code = <<<EOD
<div class="php" id="bar">
	<p>Lorem ipsum dolor</p>
</div>
EOD;
		$testable = $this->_testable(array(
			'source' => $code,
			'wrap' => false,
		));

		$this->assertIdentical(false, $testable->isPHP(1));
		$this->assertIdentical(false, $testable->isPHP(2));
	}

	public function testLineIsNotPHPWithInlinePHP() {
		$code = <<<EOD
<div class="php" id="bar">
	<p>Lorem <?=\$ipsum; ?> dolor</p>
</div>
EOD;
		$testable = $this->_testable(array(
			'source' => $code,
			'wrap' => false,
		));

		$this->assertIdentical(false, $testable->isPHP(1));
		$this->assertIdentical(false, $testable->isPHP(2));
	}

}

?>