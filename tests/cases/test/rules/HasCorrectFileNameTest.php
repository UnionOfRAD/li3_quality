<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectFileNameTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectFileName';

	public function testNoneCamelCaseWithClass() {
		$this->assertRuleFail(array(
			'source' => 'class foobar {}',
			'path' => 'foobar.php'
		), $this->rule);
	}

	public function testNoneCamelCaseWithOutClass() {
		$this->assertRulePass(array(
			'source' => 'function foobar() {}',
			'path' => 'foobar.php'
		), $this->rule);
	}

	public function testCamelCaseWithClass() {
		$this->assertRulePass(array(
			'source' => 'class foobar {}',
			'path' => 'FooBar.php'
		), $this->rule);
	}

	public function testCamelCaseWithoutClass() {
		$this->assertRulePass(array(
			'source' => 'function foobar() {}',
			'path' => 'FooBar.php'
		), $this->rule);
	}

}

?>