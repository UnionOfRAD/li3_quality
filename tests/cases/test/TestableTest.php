<?php

namespace li3_quality\tests\cases\test;

use li3_quality\tests\mocks\test\Testable;

class TestableTest extends \li3_quality\test\Unit {

	public function testParseLines() {
		$testable = new Testable(array(
			'source' => "one\rtwo\nthree\r\nfour",
		));
		$this->assertEqual(4, count($testable->lines()));
	}

	public function testParseDoubleLinesR() {
		$testable = new Testable(array(
			'source' => "one\r\rtwo\nthree\r\nfour",
		));
		$lines = $testable->lines();
		$this->assertEqual(5, count($lines));
		$this->assert(empty($lines[1]));
	}

	public function testParseDoubleLinesN() {
		$testable = new Testable(array(
			'source' => "one\rtwo\n\nthree\r\nfour",
		));
		$lines = $testable->lines();
		$this->assertEqual(5, count($lines));
		$this->assert(empty($lines[2]));
	}

	public function testParseDoubleLinesRN() {
		$testable = new Testable(array(
			'source' => "one\rtwo\nthree\r\n\r\nfour",
		));
		$lines = $testable->lines();
		$this->assertEqual(5, count($lines));
		$this->assert(empty($lines[3]));
	}

}

?>