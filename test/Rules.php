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
	 *
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
	 *
	 * @param  object $rule     The rule to add
	 * @param  array  $options  Rule options
	 * @return void
	 */
	public static function add($rule, $options = array()) {
		$class = get_class($rule);
		$sep = strrpos($class, '\\');
		$name = ($sep !== false) ? substr($class, $sep + 1) : $class;
		static::$_rules[$name] = array(
			'rule' => $rule,
			'options' => $options,
		);
	}

	/**
	 * Will iterate over each rule calling apply on them
	 *
	 * @param   object $testable The testable object
	 * @param   array  $filters  Filter rules by name
	 * @return  array
	 */
	public static function apply($testable, array $filters = array()) {
		$violations = array();
		$warnings = array();
		$success = true;
		if (count($filters) > 0) {
			$rules = static::filterByName($filters);
		} else {
			$rules = static::$_rules;
		}

		foreach ($rules as $ruleSet) {
			$rule = $ruleSet['rule'];
			$options = $ruleSet['options'];
			$rule->apply($testable, $options);
			$warnings = array_merge($warnings, $rule->warnings());
			if (!$rule->success()) {
				$success = false;
				$violations = array_merge($violations, $rule->violations());
			}
			$rule->reset();
		}

		return compact('violations', 'success', 'warnings');
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

	/**
	 * Will reset the current set of rules
	 *
	 * @return void
	 */
	public static function reset() {
		static::$_rules = array();
	}

	/**
	 * Will find filtered rules
	 *
	 * @param  array $names Rule names to filter for
	 * @return array        Filtered rules
	 */
	public static function filterByName(array $names) {
		$filter = array_fill_keys($names, NULL);
		return array_intersect_key(static::$_rules, $filter);
	}
}

?>