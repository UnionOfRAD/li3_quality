<?php

namespace li3_quality\tests\mocks\extensions\command;

class MockSyntax extends \li3_quality\extensions\command\Syntax {

	public $outputWithStyle = array();

	public $errorWithStyle = array();

	public $mockSubjects = array('subjects');

	public $stops = array();

	public $pathResponse = false;

	public function out($output = null, $options = array('nl' => 1)) {
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

	protected function _subjects($options = array()) {
		if ($this->mockSubjects === false) {
			return parent::_subjects($options);
		}
		return $this->mockSubjects;
	}

	protected function _path($path) {
		return $this->pathResponse;
	}

	public function subjects($options = array()) {
		return $this->_subjects($options);
	}
}

?>