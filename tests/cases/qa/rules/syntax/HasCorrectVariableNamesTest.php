<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class HasCorrectVariableNamesTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\HasCorrectVariableNames';

	public function testBasicMethods() {
		$code = <<<EOD
class FooBar {
	public \$fooBar;
	public function fooBar() {
		\$booBar = false;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWithGlobal() {
		$code = <<<EOD
class FooBar {
	public \$fooBar;
	public function fooBar() {
		\$_SERVER = null;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWithCamelCase() {
		$code = <<<EOD
class FooBar {
	public \$FooBarBaz;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

}

?>