<?php

namespace li3_quality\tests\cases\test\rules;

class HasNoPrivateMethodsTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasNoPrivateMethods';

	public function testPrivateMethod() {
		$code = <<<EOD
class FooBar {
	private function foo() {}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testPrivateProperty() {
		$code = <<<EOD
class FooBar {
	private function foo() {}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testPublicMethod() {
		$code = <<<EOD
class FooBar {
	public function foo() {}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

}

?>