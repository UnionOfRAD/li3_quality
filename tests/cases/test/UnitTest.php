<?php

namespace li3_quality\tests\cases\test;

use li3_quality\tests\mocks\test\MockUnit;
use li3_quality\test\Unit;

class UnitTest extends \li3_quality\test\Unit {

	protected $_rule = 'li3_quality\tests\mocks\test\MockRule';

	public function testAssertRulePassPassing() {
		$code = <<<EOD
class foobar {
	public function bar() {
		return false;
	}
}
EOD;
		$unit = new Unit();

		$expected = true;
		$result = $unit->assertRulePass($code, $this->_rule);

		$this->assertIdentical($expected, $result);
	}

	public function testAssertRulePassFailing() {
		$code = <<<EOD
class foobar {
	public function bar() {
		return false;
	}
}
EOD;
		$unit = new MockUnit();
		$unit->rule = new $this->_rule();
		$unit->rule->addViolation(array(
			'message' => 'foobar',
			'line' => 0,
			'position' => 0,
		));

		$expected = false;
		$result = $unit->assertRulePass($code, $this->_rule);

		$this->assertIdentical($expected, $result);
	}

	public function testAssertRuleFailPassing() {
		$code = <<<EOD
class foobar {
	public function bar() {
		return false;
	}
}
EOD;
		$unit = new MockUnit();
		$unit->rule = new $this->_rule();
		$unit->rule->addViolation(array(
			'message' => 'foobar',
			'line' => 0,
			'position' => 0,
		));

		$expected = true;
		$result = $unit->assertRuleFail($code, $this->_rule);

		$this->assertIdentical($expected, $result);
	}

	public function testAssertRuleFailFailing() {
		$code = <<<EOD
class foobar {
	public function bar() {
		return false;
	}
}
EOD;
		$unit = new Unit();

		$expected = false;
		$result = $unit->assertRuleFail($code, $this->_rule);

		$this->assertIdentical($expected, $result);
	}

}

?>