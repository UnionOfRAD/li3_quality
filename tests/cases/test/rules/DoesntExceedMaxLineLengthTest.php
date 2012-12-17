<?php

namespace li3_quality\tests\cases\test\rules;

class DoesntExceedMaxLineLengthTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\DoesntExceedMaxLineLength';

	public function testSimple() {
		$code = str_repeat(' ', 10);
		$this->assertRulePass($code, $this->rule);
	}

	public function testMax() {
		$code = str_repeat(' ', 100);
		$this->assertRulePass($code, $this->rule);
	}

	public function testTooManyChars() {
		$code = str_repeat(' ', 101);
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMaxTabs() {
		$code = str_repeat("\t", 25);
		$this->assertRulePass($code, $this->rule);
	}

	public function testTooManyTabs() {
		$code = str_repeat("\t", 34);
		$this->assertRuleFail($code, $this->rule);
	}

	public function testHasWarnings() {
		$code = str_repeat(' ', 81);
		$this->assertRuleWarning($code, $this->rule);
		$this->assertRulePass($code, $this->rule);
	}

	public function testHasNoWarnings() {
		$code = str_repeat(' ', 80);
		$this->assertRuleNoWarning($code, $this->rule);
		$this->assertRulePass($code, $this->rule);
	}

}

?>