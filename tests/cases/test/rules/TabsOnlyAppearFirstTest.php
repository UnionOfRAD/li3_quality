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

}

?>