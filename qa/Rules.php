<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa;

class Rules {

	/**
	 * A list of all the rules that will be applied to the test
	 *
	 * @var array
	 */
	protected $_rules = array();

	/**
	 * Will add a single rule to the list of rules to be applied to the tests
	 *
	 * @param  object $rule     The rule to add
	 * @param  array  $options  Rule options
	 * @return void
	 */
	public function add($rule, $options = array()) {
		$this->_rules[$rule->name()] = array(
			'rule' => $rule,
			'options' => $options,
		);
	}

	/**
	 * Will iterate over each rule calling apply on them
	 *
	 * @param   object $testable The testable object
	 * @return  array
	 */
	public function apply($testable) {
		$violations = array();
		$warnings = array();
		$success = true;

		foreach ($this->_rules as $ruleSet) {
			$rule = $ruleSet['rule'];
			$options = $ruleSet['options'];

			if ($rule->enabled($testable, $options)) {
				$rule->apply($testable, $options);
				$warnings = array_merge($warnings, $rule->warnings());

				if (!$rule->success()) {
					$success = false;
					$violations = array_merge($violations, $rule->violations());
				}
			} else {
				$warnings = array_merge($warnings, array(
					'line' => '-',
					'position' => '-',
					'message' => 'Rule `' . $rule->name() . '`not enabled.',
				));
			}
			$rule->reset();
		}
		return compact('violations', 'success', 'warnings');
	}

	/**
	 * Will remove unnecessary items and add options.
	 *
	 * @param array $variables The key is the rule the value is the configs
	 * @return void
	 */
	public function options(array $variables) {
		foreach ($this->_rules as $key => &$rule) {
			if (isset($variables[$key]) && is_array($variables[$key])) {
				$rule['options'] = $variables[$key];
			}
		}
	}
}

?>