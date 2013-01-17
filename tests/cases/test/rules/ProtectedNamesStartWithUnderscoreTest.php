<?php

namespace li3_quality\tests\cases\test\rules;

class ProtectedNamesStartWithUnderscoreTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\ProtectedNamesStartWithUnderscore';

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

	public function testVariableWithoutUnderscore() {
		$code = <<<EOD
class FooBar {
	protected static \$foo = array();
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testVariableWithUnderscore() {
		$code = <<<EOD
class FooBar {
	protected static \$_config = array();
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

}

?>