<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectEncodingTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectEncoding';

	public function testNonUTF8() {
		$code = "\x80-\xBF";
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNormalUTF8() {
		$code = 'foobarbaz';
		$this->assertRulePass($code, $this->rule);
	}

}

?>