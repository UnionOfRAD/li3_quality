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
	 * Contains the current violations.
	 */
	protected $_violations = array();

	/**
	 * Contains the current violations.
	 */
	protected $_warnings = array();

	/**
	 * This method will need to addViolations if one is found
	 *
	 * @param   object $testable The testable object
	 * @return  void
	 */
	abstract public function apply($testable);

	/**
	 * Will determine if `apply()` had any violations
	 *
	 * @return  boolean
	 */
	public function success() {
		return empty($this->_violations);
	}

	/**
	 * Will add violations in the correct way
	 *
	 * @param   array $violation Violation can have message, line, and position
	 * @return  void
	 */
	public function addViolation($violation = array()) {
		$this->_violations[] = $violation + array(
			'line' => '-',
			'position' => '-',
			'message' => 'Unnamed Violation',
		);
	}

	/**
	 * Will add violations in the correct way
	 *
	 * @param   array $warning Warning can have message, line, and position
	 * @return  void
	 */
	public function addWarning($warning = array()) {
		$this->_warnings[] = $warning + array(
			'line' => '-',
			'position' => '-',
			'message' => 'Unnamed Warning',
		);
	}

	/**
	 * Will return a list of current violations
	 *
	 * @return  array
	 */
	public function violations() {
		return $this->_violations;
	}

	/**
	 * Will return a list of current violations
	 *
	 * @return  array
	 */
	public function warnings() {
		return $this->_warnings;
	}

	/**
	 * Will reset the current list of violations
	 *
	 * @return  void
	 */
	public function reset($name = null) {
		if ($name === 'violations') {
			$this->_violations = array();
		} elseif ($name === 'warnings') {
			$this->_warnings = array();
		} else {
			$this->_violations = array();
			$this->_warnings = array();
		}
	}

	/**
	 * A switch to check if this rule should be applied to the current
	 * tests or not
	 */
	public function enabled() {
		return true;
	}

	/**
	 * A helper method which helps finding tokens. If there are no tokens
	 * on this line, we go backwards assuming a multiline token.
	 *
	 * @param  int    $line   The line you are on
	 * @param  array  $tokens The tokens to iterate
	 * @return int            The token id if found, -1 if not
	 */
	protected function _findTokenByLine($line, $tokens) {
		foreach ($tokens as $id => $token) {
			if ($token['line'] === $line) {
				return $id;
			}
		}
		return $line === 0 ? -1 : $this->_findTokenByLine($line - 1, $tokens);
	}

	/**
	 * Will determine if a set of tokens is on a given line.
	 *
	 * @param  int    $line     The line you are on
	 * @param  array  $tokens   The tokens to iterate
	 * @param  array  $tokenIds The tokens you are looking for
	 * @return int              The token id if found, -1 if not
	 */
	protected function _lineHasToken($line, $tokens, array $tokenIds = array()) {
		foreach($tokens as $token) {
			$correctLine = $token['line'] === $line;
			if ($correctLine && in_array($token['id'], $tokenIds)) {
				return true;
			} elseif ($token['line'] > $line) {
				break;
			}
		}
		return false;
	}

}

?>