<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectDocblockStyleTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectDocblockStyle';

	public function testCorrectBlankLinedDocBlock() {
		$code = <<<EOD
<?php
/**
 * This is a comment
 *
 * bar
 */
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testCorrectBlankLinedDocBlockForMethod() {
		$code = <<<EOD
<?php

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
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testClassAndMethodComments() {
		$code = <<<EOD
<?php

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
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testLonelyDocBlocks() {
		$code = <<<EOD
<?php
/**
 * This is a comment
 */
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testIncorrectLonelyDocBlocks() {
		$code = <<<EOD
<?php
/**
* This is a comment
*/
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testAbstractClassComments() {
		$code = <<<EOD
<?php

/**
 * This is a comment
 */
abstract class Foo {
}
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testClassCommentsWithoutSpacing() {
		$code = <<<EOD
<?php

/**
* This is a comment
*/
class Foo {
}
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testClassCommentsWithAdditionalSpacing() {
		$code = <<<EOD
<?php

	/**
	 * This is a comment
	 */
class Foo {
}
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
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

	public function testDocBlockCorrectTagPlacement() {
		$code = <<<EOD
<?php

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
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testDocBlockIncorrectTagPlacement() {
		$code = <<<EOD
<?php

/**
 * Here is some info about class Foo
 *
 * Oh and something else...
 * @package FooPackage
 */
class Foo {
}'Docblocks should only be at the beginning of the page or before '.
						'a class/function.'
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testDocBlockMustBeLast() {
		$code = <<<EOD
<?php

/**
 * Here is some info about class Foo
 *
 * @package FooPackage
 * Oh and something else...
 */
class Foo {
}
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testMultiLineParam() {
		$code = <<<EOD
<?php

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
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testRandomDocblockBelowClass() {
		$code = <<<EOD
<?php

class Foo {
}
/**
 * Splits the provided `\$code` into PHP language tokens.
 */
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testClassConstants() {
		$code = <<<EOD
<?php

class foo {

	/**
	 * Foo
	 *
	 * @var const
	 */
	const FOO = 'bar';

}
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testStaticCallComments() {
		$code = <<<EOD
<?php

class foo {
}

/**
 * Static call
 */
foo::config();
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}
}

?>