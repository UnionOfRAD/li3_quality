<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

class Rule extends \lithium\core\Object {

	/**
	 *
	 */
	protected $_callable = null;

	/**
	 * Contains the current violations.
	 */
	protected $_violations = array();
	
	/**
	 *
	 */
	public function __construct($callable, $options = array()) {
		$this->_callable = $callable;
	}

	/**
	 *
	 */
	public function apply($testable) {
		$callable = $this->_callable;
		$result = $callable($this, $testable);
		return $result;
	}
	
	/**
	 *
	 */
	public function success() {
		return empty($this->_violations);
	}

	/**
	 *
	 */
	public function addViolation($violation = array()) {
		$this->_violations[] = $violation;
	}

	/**
	 *
	 */
	public function violations() {
		return $this->_violations;
	}

	/**
	 *
	 */
	public function reset() {
		$this->_violations = array();
	}
	
}

?>