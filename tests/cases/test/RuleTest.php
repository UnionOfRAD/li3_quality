<?php

namespace li3_quality\tests\cases\test;

use li3_quality\tests\mocks\test\MockRule as Rule;

class RuleTest extends \li3_quality\test\Unit {

	public function testAddAndRetrieveViolation() {
		$rule = new Rule();
		$violation = array(
			'message' => 'Foobar',
			'line' => 0,
			'position' => '-',
		);
		$rule->addViolation($violation);
		$violations = $rule->violations();

		$this->assertIdentical(array($violation), $violations);
	}

	public function testResetViolation() {
		$rule = new Rule();
		$violation = array(
			'message' => 'Foobar',
			'line' => 0,
		);
		$rule->addViolation($violation);
		$rule->reset();
		$violations = $rule->violations();

		$this->assertIdentical(array(), $violations);
	}

	public function testSuccessWithViolations() {
		$rule = new Rule();
		$violation = array(
			'message' => 'Foobar',
			'line' => 0,
		);
		$rule->addViolation($violation);
		$success = $rule->success();

		$this->assertIdentical(false, $success);
	}

	public function testAddAndRetrieveWarnings() {
		$rule = new Rule();
		$warning = array(
			'message' => 'Foobar',
			'line' => 0,
			'position' => '-',
		);
		$rule->addWarning($warning);
		$warnings = $rule->warnings();

		$this->assertIdentical(array($warning), $warnings);
	}

	public function testResetWarnings() {
		$rule = new Rule();
		$warning = array(
			'message' => 'Foobar',
			'line' => 0,
		);
		$rule->addViolation($warning);
		$rule->reset();
		$warnings = $rule->warnings();

		$this->assertIdentical(array(), $warnings);
	}

	public function testSuccessWithWarnings() {
		$rule = new Rule();
		$warning = array(
			'message' => 'Foobar',
			'line' => 0,
		);
		$rule->addWarning($warning);
		$success = $rule->success();

		$this->assertIdentical(true, $success);
	}

	public function testSuccessWithoutViolations() {
		$rule = new Rule();;
		$success = $rule->success();

		$this->assertIdentical(true, $success);
	}

	public function testResetJustWarnings() {
		$rule = new Rule();
		$rule->addWarning(array(
			'message' => 'Warning',
			'line' => 0,
		));
		$rule->addViolation(array(
			'message' => 'Violation',
			'line' => 0,
		));
		$rule->reset('warnings');
		$this->assertIdentical(0, count($rule->warnings()));
		$this->assertIdentical(1, count($rule->violations()));
	}

	public function testResetJustViolations() {
		$rule = new Rule();
		$rule->addWarning(array(
			'message' => 'Warning',
			'line' => 0,
		));
		$rule->addViolation(array(
			'message' => 'Violation',
			'line' => 0,
		));
		$rule->reset('violations');
		$this->assertIdentical(1, count($rule->warnings()));
		$this->assertIdentical(0, count($rule->violations()));
	}

	public function testResetBoth() {
		$rule = new Rule();
		$rule->addWarning(array(
			'message' => 'Warning',
			'line' => 0,
		));
		$rule->addViolation(array(
			'message' => 'Violation',
			'line' => 0,
		));
		$rule->reset();
		$this->assertIdentical(0, count($rule->warnings()));
		$this->assertIdentical(0, count($rule->violations()));
	}


}

?>