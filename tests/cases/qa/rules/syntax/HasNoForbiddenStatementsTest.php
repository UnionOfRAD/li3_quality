<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class HasNoForbiddenStatementsTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\HasNoForbiddenStatements';

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