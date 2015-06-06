<?php

namespace li3_quality\tests\mocks\qa;

/**
 * A mock of the Rules class
 */
class MockRules extends \li3_quality\qa\Rules {
	public static $invocations = array();
	public static $applyResponse = array(
		'success' => true, 'warnings' => array()
	);

	public static function reset() {
		static::$invocations = array();
		static::$applyResponse = array(
			'success' => true, 'warnings' => array()
		);
	}

	public static function apply($testable, array $filters = array()) {
		$resp = static::$applyResponse;
		return is_callable($resp) ? $resp() : $resp;
	}

	public static function ruleOptions(array $variables) {
		$call = array('method' => 'ruleOptions', 'args' => array($variables));
		static::$invocations[] = $call;
	}
}

?>