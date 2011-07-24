<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

use li3_quality\test\Rule;

class Rules extends \lithium\core\StaticObject {

	/**
	 *
	 */
	protected static $_rules = array();

	/**
	 *
	 */
	public function add($closure, $options = array()) {
		static::$_rules[] = new Rule($closure, $options);
	}

	/**
	 *
	 */
	public static function apply($testable) {
		$violations = array();
		$success = true;
		foreach(static::$_rules as $rule) {
			$rule->apply($testable);
			if(!$rule->success()) {
				$success = false;
				$violations += $rule->violations();
			}
			$rule->reset();
		}
		return compact('violations', 'success');
	}

	/**
	 *
	 */
	public static function get($rule = null) {
		if ($rule === null) {
			return static::$_rules;
		}
		return isset(static::$_rules[$rule]) ? static::$_rules[$rule] : null;
	}
}

?>