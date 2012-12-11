<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

use li3_quality\tests\mocks\test\Testable;

/**
 * Internal Class for testing rules
 */
class Unit extends \lithium\test\Unit  {

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
	 * Will generate a new rule and call apply on it.
	 *
	 * @param  string       $rule    The nonspaced class of the rule
	 * @param  string|array $options Source code, or arary of config options
	 * @return object
	 */
	protected function _mockRuleSuccess($rule, $options = array()) {
		$rule = $this->_rule($rule);
		$testable = $this->_testable($options);
		$rule->apply($testable);
		return $rule->success();
	}

	/**
	 * Will generate a new rule and call apply on it.
	 *
	 * @param  string       $rule    The nonspaced class of the rule
	 * @param  string|array $options Source code, or arary of config options
	 * @return object
	 */
	protected function _rule($rule) {
		$this->rule = new $rule();
		return $this->rule;
	}

	/**
	 * Will generate a new Testable object
	 *
	 * @param  string|array $options Source code, or arary of config options
	 * @return li3_quality\tests\mocks\test\Testable
	 */
	protected function _testable($options) {
		if (is_string($options)) {
			$options = array(
				'source' => $options,
			);
		}
		$options += array(
			'wrap' => true,
		);
		return new Testable($options);;
	}

}

?>