<?php

namespace li3_quality\tests\mocks\extensions\command;

class MockQuality extends \li3_quality\extensions\command\Quality {

	public $pathConfig = array();

	protected function _testables($options = array()) {
		return $this->_filter(__METHOD__, $args, function($self, $args) {
			$key = (isset($options['path'])) ? $options['path'] : $this->library;
			if (isset($this->pathConfig[$key])) {
				return $this->pathConfig[$key];
			}
			return parent::_testables($options);
		});
	}

}

?>