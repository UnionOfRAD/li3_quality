<?php

namespace li3_quality\tests\cases\test\rules;

class WeakComparisonOperatorsTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\WeakComparisonOperators';

	public function testBasicEqualComparison() {
		$code = <<<EOD
if (1 == 2) {
	return false;
}
EOD;
		$this->assertRuleWarning($code, $this->rule);
	}

	public function testBasicNonEqualComparison() {
		$code = <<<EOD
if (1 != 2) {
	return false;
}
EOD;
		$this->assertRuleWarning($code, $this->rule);
	}

	public function testBetterEqualComparison() {
		$code = <<<EOD
if (1 === 2) {
	return false;
}
EOD;
		$this->assertRuleNoWarning($code, $this->rule);
	}

	public function testBetterNonEqualComparison() {
		$code = <<<EOD
if (1 !== 2) {
	return false;
}
EOD;
		$this->assertRuleNoWarning($code, $this->rule);
	}

}

?>