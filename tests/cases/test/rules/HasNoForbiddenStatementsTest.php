<?php

namespace li3_quality\tests\cases\test\rules;

class HasNoForbiddenStatementsTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasNoForbiddenStatements';

	public function testUnsafeStatement() {
		$code = '$variable = eval("return true;");';
		$this->assertRuleWarning($code, $this->rule);
	}

	public function testForbiddenStatement() {
		$code = 'print "hello";';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNonForbiddenStatement() {
		$code = "echo 'foo';";
		$this->assertRulePass($code, $this->rule);
	}

	public function testVardump() {
		$code = "var_dump(\$foo);";
		$this->assertRuleFail($code, $this->rule);
	}

}

?>