<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectCastSpacingTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectCastSpacing';

	public function testCorrectCasting() {
		$code = '(object) $var;';
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectCastingNoSpace() {
		$code = '(object)$var;';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectCastingExtraSpace() {
		$code = '(object)  $var;';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectCastingWithString() {
		$code = '(int) "foobar";';
		$this->assertRulePass($code, $this->rule);
	}

	public function testInCorrectCastingWithString() {
		$code = '(int)"foobar";';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectCastingWithNumber() {
		$code = '(float) 10;';
		$this->assertRulePass($code, $this->rule);
	}

	public function testInCorrectCastingWithNumber() {
		$code = '(float)10;';
		$this->assertRuleFail($code, $this->rule);
	}

}

?>