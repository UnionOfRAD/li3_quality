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
	 * Where the testable object will be stored
	 * @var object
	 */
	public $testable;

	/**
	 * Where the rule object will be stored
	 * @var object
	 */
	public $rule;

	/**
	 * Will return true if the rule passed based on the provided source
	 *
	 * @param  string $source  The source to test against
	 * @param  string $rule    The nonspaced class of the rule
	 * @param  string $message The error message to throw upon failure
	 * @return bool
	 */
	public function assertRulePass($source, $rule, $message = '{:message}') {
		return $this->assert($this->_mockRule($rule, $source), $message);
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
		return $this->assert(!$this->_mockRule($rule, $source), $message);
	}

	/**
	 * Will generate a new rule and call apply on it.
	 *
	 * @param  string $rule    The nonspaced class of the rule
	 * @param  string $source  The source to test against
	 * @return bool
	 */
	protected function _mockRule($rule, $source) {
		$this->rule = new $rule();
		$this->testable = new Testable(array(
			'source' => $source,
		));
		$this->rule->apply($this->testable);
		return $this->rule->success();
	}

}