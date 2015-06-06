<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class HasCorrectEncodingTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\HasCorrectEncoding';

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