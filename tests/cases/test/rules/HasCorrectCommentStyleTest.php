<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectCommentStyleTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectCommentStyle';

	public function testCorrectBlankLinedDocBlock() {
		$code = <<<EOD
/**
 * This is a comment
 *
 * bar
 */
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectBlankLinedDocBlockForMethod() {
		$code = <<<EOD
class foo {
	/**
	 * This is a comment
	 *
	 * bar
	 */
	public function bar() {
		return false;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testClassAndMethodComments() {
		$code = <<<EOD
/**
 * This is a comment
 */
class Foo {

	/**
	 * This is another comment
	 */
	public function bar() {
		return false;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testLonelyDocBlocks() {
		$code = <<<EOD
/**
 * This is a comment
 */
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectLonelyDocBlocks() {
		$code = <<<EOD
/**
* This is a comment
*/
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testAbstractClassComments() {
		$code = <<<EOD
/**
 * This is a comment
 */
abstract class Foo {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testClassCommentsWithoutSpacing() {
		$code = <<<EOD
/**
* This is a comment
*/
class Foo {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testClassCommentsWithAdditionalSpacing() {
		$code = <<<EOD
	/**
	 * This is a comment
	 */
class Foo {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMethodCommentsWithNoSpacing() {
		$code = <<<EOD
class Foo {

	/**
	* This is another comment
	*/
	public function bar() {
		return false;
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMethodCommentsWithAdditionalSpacing() {
		$code = <<<EOD
class Foo {

		/**
		 * This is another comment
		 */
	public function bar() {
		return false;
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

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
}
?>