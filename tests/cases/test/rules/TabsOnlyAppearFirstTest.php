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

}

?>