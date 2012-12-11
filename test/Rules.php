<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

use lithium\core\Libraries;
use li3_quality\test\Rule;

class Rules extends \lithium\core\StaticObject {

	/**
	 * A list of all the rules that will be applied to the test
	 * @var array
	 */
	protected static $_rules = array();

	/**
	 * Will construct the rules to be applied and other config options
	 *
	 * @return  void
	 */
	public static function __init() {
		$rulePaths = Libraries::locate('rules');
		foreach ($rulePaths as $rulePath) {
			$rule = new $rulePath();
			if ($rule->enabled()) {
				static::add($rule);
			}
		}
	}

	/**
	 * Will add a single rule to the list of rules to be applied to the tests
	 * @param  object $rule     The rule to add
	 * @param  array  $options  Rule options
	 * @return void
	 */
	public static function add($rule, $options = array()) {
		static::$_rules[] = $rule;
	}

	/**
	 * Will iterate over each rule calling apply on them
	 *
	 * @param   object $testable The testable object
	 * @return  array
	 */
	public static function apply($testable) {
		$violations = array();
		$success = true;

		foreach (static::$_rules as $rule) {
			$rule->apply($testable);
			if (!$rule->success()) {
				$success = false;
				$violations = array_merge($violations, $rule->violations());
			}
			$rule->reset();
		}

		return compact('violations', 'success');
	}

	/**
	 * Will find a specific rule or all rules
	 *
	 * @param  string $rule The rule name
	 * @return mixed        The object of the rule you are looking for, or null
	 */
	public static function get($rule = null) {
		if ($rule === null) {
			return static::$_rules;
		}
		return isset(static::$_rules[$rule]) ? static::$_rules[$rule] : null;
	}
}

?>