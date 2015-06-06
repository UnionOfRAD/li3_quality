<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\extensions\test\filter;

use lithium\core\Libraries;
use li3_quality\qa\Rules;
use li3_quality\qa\Testable;

class Syntax extends \lithium\test\Filter {

	public static function apply($report, $tests, array $options = array()) {
		$rules = new Rules();

		$file = Libraries::get('li3_quality', 'path') . '/config/syntax.json';
		$config = json_decode(file_get_contents($file), true) + array(
			'name' => null,
			'rules' => array(),
			'options' => array()
		);

		foreach ($config['rules'] as $ruleName) {
			$class = Libraries::locate('rules.syntax', $ruleName);
			$rules->add(new $class());
		}
		if ($config['options']) {
			$rules->options($config['options']);
		}

		foreach ($tests->invoke('subject') as $class) {
			$report->collect(__CLASS__, array(
				$class => $rules->apply(new Testable(array('path' => Libraries::path($class))))
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