<?php

namespace li3_quality\tests\cases\test\rules;

class HasNoForbiddenStatementsTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasNoForbiddenStatements';

	public function testForbiddenStatement() {
		$code = 'eval("ls");';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNonForbiddenStatement() {
		$code = "echo 'foo';";
		$this->assertRulePass($code, $this->rule);
	}

	public function testvardump() {
		$code = "var_dump(\$foo);";
		$this->assertRuleFail($code, $this->rule);
	}

}

?>