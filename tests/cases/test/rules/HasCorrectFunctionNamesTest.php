<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectFunctionNamesTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectFunctionNames';

	public function testBasicMethods() {
		$code = <<<EOD
class FooBar {
	public function fooBar() {}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWithMagic() {
		$code = <<<EOD
class FooBar {
	public function __call() {}
	public function fooBar() {}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWithCamelCase() {
		$code = <<<EOD
class FooBar {
	public function FooBarBaz() {}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectProtectedMethod() {
		$code = <<<EOD
class FooBar {
	public function _fooBarBaz() {}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

}

?>