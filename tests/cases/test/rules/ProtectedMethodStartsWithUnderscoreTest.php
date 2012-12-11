<?php

namespace li3_quality\tests\cases\test\rules;

class ProtectedMethodStartsWithUnderscoreTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\ProtectedMethodStartsWithUnderscore';

	public function testWithoutWhiteSpace() {
		$code = <<<EOD
class FooBar {
	protected function _fooBar() {}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWithTabs() {
		$code = <<<EOD
class FooBar {
	protected function fooBar() {}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

}

?>