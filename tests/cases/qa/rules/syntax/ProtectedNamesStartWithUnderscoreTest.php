<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class ProtectedNamesStartWithUnderscoreTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\ProtectedNamesStartWithUnderscore';

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

	public function testUnderscoreIsNotRequiredForException() {
		$code = <<<EOD
class FooBarException extend Exception {
	protected string \$message ;
	protected int \$code ;
	protected string \$file ;
	protected int \$line ;
}
EOD;
		$this->assertRuleWarning($code, $this->rule);
	}
}

?>