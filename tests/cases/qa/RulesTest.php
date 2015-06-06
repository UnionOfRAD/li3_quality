<?php

namespace li3_quality\tests\cases\qa;

use li3_quality\qa\Rules;
use li3_quality\tests\mocks\qa\MockRule;
use li3_quality\tests\mocks\qa\MockTestable;

class RulesTest extends \li3_quality\test\Rule {

	public function setUp() {
		$this->subject = new Rules();
	}

	public function testAddRule() {
		$this->subject->add(new MockRule());
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
		$this->subject->add($rule);

		$results = $this->subject->apply($testable);

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
		$this->subject->add($rule);

		$results = $this->subject->apply($testable);

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
		$this->subject->add($rule);

		$results = $this->subject->apply($testable);

		$this->assertIdentical(array($violation), $results['violations']);
		$this->assertIdentical(array(), $results['warnings']);
		$this->assertIdentical(false, $results['success']);
	}
}

?>