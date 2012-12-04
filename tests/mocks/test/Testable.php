<?php

namespace li3_quality\tests\mocks\test;

/**
 * A mock of the Testable object
 */
class Testable extends \li3_quality\test\Testable {

	/**
	 * Overwrites the default Testable constructor
	 */
	public function __construct($config) {
		$this->_config = $config;
		$this->_source = $config['source'];
	}
}

?>