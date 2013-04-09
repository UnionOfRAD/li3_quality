<?php

namespace li3_quality\tests\mocks\test;

/**
 * A mock of the Dispather class
 */
class MockDispatcher extends \lithium\test\Dispatcher {
	public static $coverage = NULL;

	public static function reset() {
		static::$coverage = NULL;
	}

	public static function run($group = null, array $options = array()) {
		$report = static::_report($group, $options);
		$coverage = 'lithium\test\filter\Coverage';
		$report->results['filters'][$coverage] = static::$coverage;
		return $report;
	}
}

?>