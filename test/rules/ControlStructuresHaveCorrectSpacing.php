<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use li3_quality\test\Testable;
use lithium\util\String;

class ControlStructuresHaveCorrectSpacing extends \li3_quality\test\Rule {

	/**
	 * Items that help identify the correct patterns and error messages.
	 *
	 * @var array
	 */
	protected $_tokenMap = array(
		T_IF => array(
			'message' => 'Unexpected T_IF format. Should be: "if (...) {" or "} else if () {"',
			'patterns' => array(
				"/^{:whitespace}if {:bracket} {\$/",
				"/^{:whitespace}} else if {:bracket} {\$/",
			),
		),
		T_ELSEIF => array(
			'message' => 'Unexpected T_ELSE format. Should be: "} elseif (...) {"',
			'patterns' => array(
				"/^{:whitespace}} elseif {:bracket} {\$/",
			),
		),
		T_ELSE => array(
			'message' => 'Unexpected T_ELSE format. Should be: "} else {"',
			'patterns' => array(
				"/^{:whitespace}} else {\$/",
				"/^{:whitespace}} else if {:bracket} {\$/",
			),
		),
		T_DO => array(
			'message' => 'Unexpected T_DO format. Should be: "do {"',
			'patterns' => array(
				"/^{:whitespace}do {\$/",
			),
		),
		T_WHILE => array(
			'message' => 'Unexpected T_WHILE format. Should be: "while (...) {" or "} while (...);',
			'patterns' => array(
				"/^{:whitespace}while {:bracket} {\$/",
				"/^{:whitespace}} while {:bracket};\$/",
			),
		),
		T_FOR => array(
			'message' => 'Unexpected T_FOR format. Should be: "for (...) {"',
			'patterns' => array(
				"/^{:whitespace}for {:bracket} {\$/",
			),
		),
		T_FOREACH => array(
			'message' => 'Unexpected T_FOREACH format. Should be: "foreach (...) {"',
			'patterns' => array(
				"/^{:whitespace}foreach {:bracket} {\$/",
			),
		),
		T_SWITCH => array(
			'message' => 'Unexpected T_SWITCH format. Should be: "switch (...) {"',
			'patterns' => array(
				"/^{:whitespace}switch {:bracket} {\$/",
			),
		),
		T_CASE => array(
			'message' => 'Unexpected T_CASE format. Should be: "case ...:"',
			'patterns' => array(
				"/^{:whitespace}case .*:\$/",
			),
		),
		T_DEFAULT => array(
			'message' => 'Unexpected T_SWITCH format. Should be: "default:"',
			'patterns' => array(
				"/^{:whitespace}default:\$/",
			),
		),
	);

	/**
	 * Reusable expressions to make code easier to read and reusable
	 * @var array
	 */
	protected $_regexMap = array(
		'whitespace' => '(\s+)?',
		'bracket'    => '\(([^\s].*[^\s]|[^\s]+)\)',
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
				$patterns = $tokenMap['patterns'];
				$lineNumber = $token['line'];
				if ($this->_matchPattern($lines, $lineNumber, $patterns, $token['id']) === false) {
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
	protected function _matchPattern(array $lines, $lineNumber, $patterns, $token) {
		$line = $lines[$lineNumber-1];
		foreach ($patterns as $pattern) {
			if (preg_match(String::insert($pattern, $this->_regexMap), $line) === 1) {
				return true;
			}
		}
		return false;
	}

}

?>