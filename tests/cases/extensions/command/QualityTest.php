<?php

namespace li3_quality\tests\cases\extensions\command;

use li3_quality\extensions\command\Quality;
use li3_quality\tests\mocks\extensions\command\MockQuality;
use li3_quality\test\Rules;
use lithium\console\Request;

class QualityTest extends \li3_quality\test\Unit {

	public function setUp() {
		$this->classes = array(
			'response' => 'lithium\tests\mocks\console\MockResponseRRR'
		);
		// $this->_backup['cwd'] = getcwd();
		// $this->_backup['_SERVER'] = $_SERVER;
		// $_SERVER['argv'] = array();

		// chdir(LITHIUM_LIBRARY_PATH . '/lithium');

		$this->request = new Request(array('input' => fopen('php://temp', 'w+')));
		// $this->request->params = array('library' => 'build_test');
	}

	public function tearDown() {
		// $_SERVER = $this->_backup['_SERVER'];
		// chdir($this->_backup['cwd']);
	}

	public function checkDefaultInput() {
		return;
		$quality = new Quality(array(
			'request' => $this->request, 'classes' => $this->classes
		));
		$this->assertIdentical(true, $quality->library);
		$this->assertIdentical(100, $quality->threshold);
	}

	public function testOverwriteDefaults() {
		return;
		$this->request->params = array(
			'library' => 'build_test',
			'threshold' => 80,
		);
		$quality = new Quality(array(
			'request' => $this->request, 'classes' => $this->classes
		));

		$this->assertIdentical('build_tesst', $quality->library);
		$this->assertIdentical(80, $quality->threshold);
	}

	public function testTest() {
		return;
		$quality = new Quality(array(
			'request' => $this->request, 'classes' => $this->classes
		));

		$quality->run();
		$result = $quality->response->output;

		$this->assertPattern("/li3 quality syntax/", $result);
		$this->assertPattern("/li3 quality documented/", $result);
		$this->assertPattern("/li3 quality coverage/", $result);
	}

	public function testRuleCountInOutput() {
		return;
		$mockRule = new Rules;
		$this->classes['rules'] = $mockRule;
		print_r($this->classes);
		$mockQuality = new MockQuality(array(
			'request' => $this->request, 'classes' => $this->classes
		));
		$mockQuality->_testables = function($options, $parent) {
			echo 'meow';
			exit;
			return array();
		};

		$mockQuality->syntax(null);
		$result = $mockQuality->response->output;
	}

}

?>