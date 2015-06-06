<?php

namespace li3_quality\tests\cases\test\rules\syntax;

class HasCorrectFunctionNamesTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\syntax\HasCorrectFunctionNames';

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
	protected function _fooBarBaz() {}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testInCorrectPublicMethod() {
		$code = <<<EOD
class FooBar {
	public function _fooBarBaz() {}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMagicMethods() {
		$code = <<<EOD
class FooBar {
	public function __construct() {}
	public function __destruct() {}
	public function __call() {}
	public static function __callStatic() {}
	public function __get() {}
	public function __set() {}
	public function __isset() {}
	public function __unset() {}
	public function __sleep() {}
	public function __wakeup() {}
	public function __toString() {}
	public function __invoke() {}
	public static function __set_state() {}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

}

?>