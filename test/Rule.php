<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

abstract class Rule extends \lithium\core\Object {

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
	public function __construct($options = array()) {}

	/**
	 *
	 */
	abstract public function apply($testable);

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

	/**
	 *
	 */
	public function enabled() {
		return true;
	}

}

?>