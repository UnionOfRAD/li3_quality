<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\extensions\test\filter;

use lithium\core\Libraries;
use li3_quality\test\Rules;
use li3_quality\test\Testable;

class Syntax extends \lithium\test\Filter {

	public static function apply($report, $tests, array $options = array()) {
		$config = Libraries::get('li3_quality');

		$ruleConfig = $config['path'] . '/config/syntax.json';
		$ruleConfig = json_decode(file_get_contents($ruleConfig), true);

		$filters = $ruleConfig['rules'];

		if (isset($ruleConfig['variables'])) {
			Rules::ruleOptions($ruleConfig['variables']);
		}

		foreach ($tests->invoke('subject') as $class) {
			$report->collect(__CLASS__, array(
				$class => Rules::apply(new Testable(array('path' => Libraries::path($class))), $filters)
			));
		}
		return $tests;
	}

	public static function analyze($report, array $options = array()) {
		$results = $report->results['filters'][__CLASS__];
		$metrics = array();
		foreach ($results as $result) {
			foreach ($result as $class => $metric) {
				$metrics[$class] = array(
					'violations' => $metric['violations'],
					'warnings' => $metric['warnings'],
				);
			}
		}
		return $metrics;
	}
}

?>