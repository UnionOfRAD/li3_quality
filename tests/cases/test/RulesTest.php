<?php

namespace li3_quality\tests\cases\test;

use li3_quality\test\Rules;
use li3_quality\tests\mocks\test\MockRule;
use li3_quality\tests\mocks\test\MockTestable;
use stdClass;

class RulesTest extends \li3_quality\test\Unit {

	public function tearDown() {
		Rules::reset();
	}

	public function testAddRule() {
		$newRule = new stdClass();
		Rules::add($newRule);

		$addedRules = Rules::get();
		$lastRuleSet = array_pop($addedRules);

		$this->assertIdentical($newRule, $lastRuleSet['rule']);
	}

	public function testReset() {
		Rules::add(new stdClass());

		Rules::reset();
		$addedRules = Rules::get();

		$this->assertIdentical(0, count($addedRules));
	}

	public function testGetNamedRule() {
		$newRule = new stdClass();
		Rules::add($newRule);

		$addedRules = Rules::get('stdClass');

		$this->assertIdentical($newRule, $addedRules['rule']);
	}

	public function testGetNamedRuleWithNoName() {
		$this->assertIdentical(null, Rules::get('badname'));
	}
	public function testApplyBasic() {
		$rule = new MockRule();
		$testable = new MockTestable(array(
			'wrap' => false,
			'source' => <<<EOD
<?php
echo 'foobar';
EOD
		));
		Rules::add($rule);

		$results = Rules::apply($testable);

		$this->assertIdentical(array(), $results['violations']);
		$this->assertIdentical(array(), $results['warnings']);
		$this->assertIdentical(true, $results['success']);
	}

	public function testApplyWithWarning() {
		$rule = new MockRule();
		$warning = array(
			'message' => 'foobar',
			'line' => 0,
			'position' => 0,
		);
		$rule->addWarning($warning);
		$testable = new MockTestable(array(
			'wrap' => false,
			'source' => <<<EOD
<?php
echo 'foobar';
EOD
		));
		Rules::add($rule);

		$results = Rules::apply($testable);

		$this->assertIdentical(array(), $results['violations']);
		$this->assertIdentical(array($warning), $results['warnings']);
		$this->assertIdentical(true, $results['success']);
	}

	public function testApplyWithViolation() {
		$rule = new MockRule();
		$violation = array(
			'message' => 'foobar',
			'line' => 0,
			'position' => 0,
		);
		$rule->addViolation($violation);
		$testable = new MockTestable(array(
			'wrap' => false,
			'source' => <<<EOD
<?php
echo 'foobar';
EOD
		));
		Rules::add($rule);

		$results = Rules::apply($testable);

		$this->assertIdentical(array($violation), $results['violations']);
		$this->assertIdentical(array(), $results['warnings']);
		$this->assertIdentical(false, $results['success']);
	}

}

?>