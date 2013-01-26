<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

use li3_quality\tests\mocks\test\MockTestable;

/**
 * Internal Class for testing rules
 */
class Unit extends \lithium\test\Unit {

	/**
	 * The rule that is being tested against.
	 */
	public $rule = null;

	/**
	 * Will return true if the rule passed based on the provided source
	 *
	 * @param  string $source  The source to test against
	 * @param  string $rule    The nonspaced class of the rule
	 * @param  string $message The error message to throw upon failure
	 * @return bool
	 */
	public function assertRulePass($source, $rule, $message = '{:message}') {
		return $this->assert($this->_mockRuleSuccess($rule, $source), $message, array(
			'expected' => 'pass',
			'result' => $this->rule->violations(),
		));
	}

	/**
	 * Will return true if the rule failed based on the provided source
	 *
	 * @param  string $source  The source to test against
	 * @param  string $rule    The nonspaced class of the rule
	 * @param  string $message The error message to throw upon failure
	 * @return bool
	 */
	public function assertRuleFail($source, $rule, $message = '{:message}') {
		return $this->assert(!$this->_mockRuleSuccess($rule, $source), $message, array(
			'expected' => 'fail',
			'result' => $this->rule->violations(),
		));
	}

	/**
	 * Will return true if the rule passed based on the provided source
	 *
	 * @param  string $source  The source to test against
	 * @param  string $rule    The nonspaced class of the rule
	 * @param  string $message The error message to throw upon failure
	 * @return bool
	 */
	public function assertRuleWarning($source, $rule, $message = '{:message}') {
		$this->_mockRuleSuccess($rule, $source);
		return $this->assert(count($this->rule->warnings()) > 0, $message, array(
			'expected' => 'pass',
			'result' => $this->rule->warnings(),
		));
	}

	/**
	 * Will return true if the rule failed based on the provided source
	 *
	 * @param  string $source  The source to test against
	 * @param  string $rule    The nonspaced class of the rule
	 * @param  string $message The error message to throw upon failure
	 * @return bool
	 */
	public function assertRuleNoWarning($source, $rule, $message = '{:message}') {
		$this->_mockRuleSuccess($rule, $source);
		return $this->assert(count($this->rule->warnings()) === 0, $message, array(
			'expected' => 'fail',
			'result' => $this->rule->warnings(),
		));
	}

	/**
	 * Will generate a new rule and call apply on it.
	 *
	 * @param  string       $rule    The nonspaced class of the rule
	 * @param  string|array $options Source code, or arary of config options
	 * @return object
	 */
	protected function _mockRuleSuccess($rule, $options = array()) {
		if (!is_array($options)) {
			$options = array(
				'source' => $options,
			);
		}
		$rule = $this->_rule($rule, $options);
		$testable = $this->_testable($options);
		$rule->apply($testable);
		return $rule->success();
	}

	/**
	 * Will generate a new rule and call apply on it.
	 *
	 * @param  string $rule    The nonspaced class of the rule
	 * @param  array  $options Source code, or arary of config options
	 * @return object
	 */
	protected function _rule($rule, array $options = array()) {
		$this->rule = new $rule($options);
		return $this->rule;
	}

	/**
	 * Will generate a new Testable object
	 *
	 * @param  array $options Source code, or arary of config options
	 * @return object
	 */
	protected function _testable(array $options = array()) {
		$options += array(
			'wrap' => true,
		);
		return new MockTestable($options);
	}

}

?>