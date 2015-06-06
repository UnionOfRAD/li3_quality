<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class HasCorrectPermissionsTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\tests\mocks\qa\rules\syntax\MockHasCorrectPermissions';

	public function testNonExecuteableFile() {
		$this->assertRulePass(array(
			'source' => null,
			'path' => __FILE__,
			'executeable' => false,
		), $this->rule);
	}

	public function testExecuteableFile() {
		$this->assertRuleFail(array(
			'source' => null,
			'path' => __FILE__,
			'executeable' => true,
		), $this->rule);
	}

}

?>