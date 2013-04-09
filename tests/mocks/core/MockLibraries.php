<?php

namespace li3_quality\tests\mocks\core;

/**
 * A mock of the Libraries class
 */
class MockLibraries extends \lithium\core\Libraries {
	public static $findResponse = array('testables');
	public static $invocations = array();

	public static function reset() {
		static::$findResponse = array('testables');
		static::$invocations = array();
	}

	public static function find($library, array $options = array()) {
		$call = array('method' => 'find', 'args' => array($library, $options));
		static::$invocations[] = $call;
		return static::$findResponse;
	}
}

?>