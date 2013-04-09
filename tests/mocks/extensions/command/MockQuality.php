<?php

namespace li3_quality\tests\mocks\extensions\command;

class MockQuality extends \li3_quality\extensions\command\Quality {
	public $outputWithStyle = array();
	public $errorWithStyle = array();
	public $mockTestables = array('testables');
	public $stops = array();
	public $pathResponse = false;

	public function out($output = null, $options = array('nl' => 1)) {
		if ($this->silent) {
			return;
		}
		$this->outputWithStyle[] = array($output, $options);
		return parent::out($output, $options);
	}

	public function error($error = null, $options = array('nl' => 1)) {
		$this->errorWithStyle[] = array($error, $options);
		return parent::error($error, $options);
	}

	public function stop($status = 0, $message = null) {
		$this->stops[] = array($status, $message);
	}

	protected function _testables($options = array()) {
		if ($this->mockTestables === false) {
			return parent::_testables($options);
		}
		return $this->mockTestables;
	}

	protected function _path($path) {
		return $this->pathResponse;
	}
}

?>