<?php

namespace li3_quality\tests\mocks\test;

/**
 * A mock of the Group class
 */
class MockGroup extends \lithium\test\Group {
	public static $all = array('testables');

	public static function reset() {
		static$all = array('testables');
	}

	public static function all(array $options = array()) {
		return static::$all;
	}
}

?>