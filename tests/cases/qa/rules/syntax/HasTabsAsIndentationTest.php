<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class HasTabsAsIndentationTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\HasTabsAsIndentation';

	public function testWithoutWhiteSpace() {
		$code = <<<EOD
class FooBar {
    public function foo() {}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testWithTabs() {
		$code = <<<EOD
class FooBar {
	public function foo() {}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

}

?>