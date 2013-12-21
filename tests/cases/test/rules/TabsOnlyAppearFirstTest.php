<?php

namespace li3_quality\tests\cases\test\rules;

class TabsOnlyAppearFirstTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\TabsOnlyAppearFirst';

	public function testBasicLines() {
		$code = <<<EOD
\$arr = array(
	'foo'    => 'bar',
	'foobar' => 'bar',
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testBasicLinesWithTabbingForIndention() {
		$code = <<<EOD
\$arr = array(
	'foo'	=> 'bar',
	'foobar' => 'bar',
);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testBlankLines() {
		$code = <<<EOD
\$arr = array(
	'foo'	=> 'bar',
	'foobar' => 'bar',
);

\$foo = 'bar';
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIgnoreComments() {
		$code = <<<EOD
/**
 * Should ignore my code snippet
 * {{{
 * class foo {
 * 	\$bar = 'baz';
 * }
 * }}}
 */
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreLineComments() {
		$code = <<<EOD
// foo	bar	baz
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreHeredoc() {
		$code = <<<EOD
<<<EOT
this
	is
		a
			test
EOT;

EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreString() {
		$code = <<<EOD
\$array = array(
	'	hello worl!'
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWhitespaceWithEndLineAndTab() {
		$code = <<<EOD
class MyClass {
	/**
	 * Overwrites the default Testable constructor
	 */
	public function super() {
		return true;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWhitespaceWithEndLineAndTabFollowedBySpace() {
		$code = <<<EOD
if (true) {
	\$array = array(
		'message' => 'Docblocks should only be at the beginning of the page or ' .
		             'before a class/function.'
	));
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWindowsStyleLineEndings() {
		$code = implode(null, array(
			"\$arr = array(",
			"\r\n\t'foo' => 'bar',",
			"\r\n);",
		));

		$this->assertRulePass($code, $this->rule);
	}
}

?>