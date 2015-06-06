<?php

namespace li3_quality\tests\cases\test;

use li3_quality\tests\mocks\test\MockRule;
use li3_quality\test\Rule;

class RuleTest extends \li3_quality\test\Rule {

	protected $_rule = 'li3_quality\tests\mocks\qa\MockRule';

	public function testAssertRulePassPassing() {
		$code = <<<EOD
class foobar {
	public function bar() {
		return false;
	}
}
EOD;
		$unit = new Rule();

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
		$unit = new MockRule();
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
		$unit = new MockRule();
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
		$unit = new Rule();

		$expected = false;
		$result = $unit->assertRuleFail($code, $this->_rule);

		$this->assertIdentical($expected, $result);
	}

	public function testAssertHasWarnings() {
		$code = <<<EOD
class foobar {
	public function bar() {
		return false;
	}
}
EOD;
		$unit = new MockRule();
		$unit->rule = new $this->_rule();
		$unit->rule->addWarning(array(
			'message' => 'foobar',
			'line' => 0,
			'position' => 0,
		));

		$expected = true;
		$result = $unit->assertRuleWarning($code, $this->_rule);

		$this->assertIdentical($expected, $result);
	}

	public function testAssertHasNoWarnings() {
		$code = <<<EOD
class foobar {
	public function bar() {
		return false;
	}
}
EOD;
		$unit = new MockRule();
		$unit->rule = new $this->_rule();

		$expected = true;
		$result = $unit->assertRuleNoWarning($code, $this->_rule);

		$this->assertIdentical($expected, $result);
	}

}

?>