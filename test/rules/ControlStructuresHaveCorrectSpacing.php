<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use li3_quality\test\Testable;

class ControlStructuresHaveCorrectSpacing extends \li3_quality\test\Rule {

	/**
	 * Items that help identify the correct patterns and error messages.
	 * 
	 * @var array
	 */
	protected $_tokenMap = array(
		T_IF => array(
			'message' => 'Unexpected T_IF format. Should be: "if (...) {"',
			'patterns' => array(
				"/^(\s+)?if \(.*\) {\$/",
				"/^(\s+)?} else if \(.*\) {\$/",
			),
		),
		T_ELSEIF => array(
			'message' => 'Unexpected T_ELSE format. Should be: "} elseif (...) {"',
			'patterns' => array(
				"/^(\s+)?} elseif \(.*\) {\$/",
			),
		),
		T_ELSE => array(
			'message' => 'Unexpected T_ELSE format. Should be: "} else {"',
			'patterns' => array(
				"/^(\s+)?} else {\$/",
				"/^(\s+)?} else if \(.*\) {\$/",
			),
		),
		T_WHILE => array(
			'message' => 'Unexpected T_WHILE format. Should be: "while (...) {"',
			'patterns' => array(
				"/^(\s+)?while \(.*\) {\$/",
			),
		),
		T_FOR => array(
			'message' => 'Unexpected T_FOR format. Should be: "for (...) {"',
			'patterns' => array(
				"/^(\s+)?for \(.*\) {\$/",
			),
		),
		T_FOREACH => array(
			'message' => 'Unexpected T_FOREACH format. Should be: "foreach (...) {"',
			'patterns' => array(
				"/^(\s+)?foreach \(.*\) {\$/",
			),
		),
	);

	/**
	 * Will iterate the given tokens finding them based on the keys of self::$_tokenMap.
	 * Upon finding the matching tokens it will attempt to match the line against a regular
	 * expression proivded in tokenMap and if none are found add a violation from the message
	 * provided in tokenMap.
	 * 
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$lines = $testable->lines();
		$tokens = $testable->tokens();
		foreach ($tokens as $token) {
			if (isset($this->_tokenMap[$token['id']])) {
				$tokenMap = $this->_tokenMap[$token['id']];
				$line = $lines[$token['line']-1];
				if ($this->matchPattern($lines, $token['line'], $tokenMap['patterns'], $token['id']) === false) {
					$this->addViolation(array(
						'message' => $this->_tokenMap[$token['id']]['message'],
						'line' => $token['line'],
					));
				}
			}
		}
	}

	/**
	 * Abstracts the matching out. Will return true if any of the patterns match correctly.
	 *
	 * @param  array $lines      An array of lines from the file.
	 * @param  int   $lineNumber The line number we found our token on.
	 * @param  array $tokenMap   An array of patterns to match against.
	 * @return bool
	 */
	protected function matchPattern(array $lines, $lineNumber, $patterns, $token) {
		$line = $lines[$lineNumber-1];
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $line) === 1) {
				return true;
			}
		}
		return false;
	}

}

?>