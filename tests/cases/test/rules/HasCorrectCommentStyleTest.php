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

	public function testDocBlockCorrectTagPlacement() {
		$code = <<<EOD
/**
 * Here is some info about class Foo
 *
 * Oh and something else...
 *
 * @package FooPackage
 */
class Foo {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testDocBlockIncorrectTagPlacement() {
		$code = <<<EOD
/**
 * Here is some info about class Foo
 *
 * Oh and something else...
 * @package FooPackage
 */
class Foo {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testDocBlockMustBeLast() {
		$code = <<<EOD
/**
 * Here is some info about class Foo
 *
 * @package FooPackage
 * Oh and something else...
 */
class Foo {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMultiLineParam() {
		$code = <<<EOD
/**
 * Splits the provided `\$code` into PHP language tokens.
 *
 * @param string \$code Source code to be tokenized.
 * @param array \$options Options consists of:
 *        -'wrap': Boolean indicating whether or not to wrap the supplied
 *          code in PHP tags.
 *        -'ignore': An array containing PHP language tokens to ignore.
 *        -'include': If supplied, an array of the only language tokens
 *         to include in the output.
 * @return array An array of tokens in the supplied source code.
 */
class Foo {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}
}
?>