<?php

namespace li3_quality\tests\cases\test;

use li3_quality\tests\mocks\test\MockRule as Rule;

class RuleTest extends \li3_quality\test\Unit {

	public function testAddAndRetrieveViolation() {
		$rule = new Rule();
		$violation = array(
			'message' => 'Foobar',
			'line' => 0,
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

	public function testSuccessWithoutViolations() {
		$rule = new Rule();;
		$success = $rule->success();

		$this->assertIdentical(true, $success);
	}

}

?>