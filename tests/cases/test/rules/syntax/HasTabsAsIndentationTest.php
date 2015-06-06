<?php

namespace li3_quality\tests\cases\test\rules\syntax;

class HasTabsAsIndentationTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\syntax\HasTabsAsIndentation';

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