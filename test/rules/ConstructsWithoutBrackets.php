<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;

class ConstructsWithoutBrackets extends \li3_quality\test\Rule {

	/**
	 * Language construct tokens to look for
	 *
	 * @var array
	 */
	public $inspectableTokens = array(
		T_ECHO,
		T_INCLUDE_ONCE,
		T_INCLUDE,
		T_PRINT,
		T_REQUIRE,
		T_REQUIRE_ONCE,
		T_THROW,
	);

	/**
	 * Language construct which should be on it's own line
	 *
	 * @var array
	 */
	public $ownLineTokens = array(
		T_ECHO,
		T_PRINT,
		T_THROW,
	);

	/**
	 * Tokens to skip after finding $inspectableTokens
	 *
	 * @var array
	 */
	public $pattern = "\s*[a-z_]+((\s(([^(][^;]*)|(\([^)]+\)[^;]+))(;|$))|;)";

	/**
	 * Will iterate the tokens for $inspectableTokens, once found it'll find
	 * the next content taht doesn't have the token $skipTokens and validate
	 * it has no parentheses
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$message = 'Construct {:name} should not contain parentheses and be on its own line.';
		$tokens = $testable->tokens();
		$inspectable = $testable->findAll($this->inspectableTokens);
		$lines = $testable->lines();

		foreach ($inspectable as $key) {
			$token = $tokens[$key];
			$lineIndex = $token['line'] - 1;
			if (isset($lines[$lineIndex])) {
				$line = $lines[$lineIndex];
				$next = $key + 1;
				if (in_array($token['id'], $this->ownLineTokens)) {
					$pattern = '/^' . $this->pattern . '$/';
				} else {
					$pattern = '/' . $this->pattern . '$/';
				}
				if (preg_match($pattern, $line) === 0) {
					$this->addViolation(array(
						'message' => String::insert($message, array(
							'name' => $token['name'],
						)),
						'line' => $token['line'],
					));
				}
			}
		}
	}
}

?>