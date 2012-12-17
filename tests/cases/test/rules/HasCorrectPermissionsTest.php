<?php

namespace li3_quality\tests\cases\test\rules;

use li3_quality\tests\mocks\MockHasCorrectPermissions;

class HasCorrectPermissionsTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\tests\mocks\test\rules\MockHasCorrectPermissions';

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