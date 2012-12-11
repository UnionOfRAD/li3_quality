<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectPermissionsTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectPermissions';

	public function testWithClosingTag() {
		$file = __FILE__ . '/../../../mocks/execute/ExecuteableFile.php';
		$this->assertRulePass(array(
			'source' => null,
			'path' => $file,
		), $this->rule);
	}

	public function testWithoutClosingTag() {
		$file = __FILE__ . '/../../../mocks/execute/NonExecuteableFile.php';
		$this->assertRulePass(array(
			'source' => null,
			'path' => $file,
		), $this->rule);
	}

}

?>