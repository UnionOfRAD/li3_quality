<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;

class HasExplicitPropertyAndMethodVisibility extends \li3_quality\test\Rule {

	public $inspectableTokens = array(
		T_FUNCTION,
		T_VARIABLE,
	);

	public $findTokens = array(
		T_PUBLIC,
		T_PROTECTED,
		T_PRIVATE,
	);

	/**
	 * Will iterate all the tokens looking for tokens in inspectableTokens
	 * The token needs an access modifier if it is a T_FUNCTION or T_VARIABLE
	 * and only contains a single tab. This prevents functions and variables
	 * inside methods to register violations.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$message = '{:name} has no declared visibility.';
		$tokens = $testable->tokens();
		$lines = $testable->lines();
		if ($this->hasClass($tokens)) {
			foreach ($tokens as $token) {
				$lineNumber = $token['line'];
				$isInspectable = in_array($token['id'], $this->inspectableTokens);
				$needsScope = $this->tokenNeedsScope($lines, $token);
				$lineHasVisibility = $this->lineHasVisibility($tokens, $lineNumber);
				if ($isInspectable && $needsScope && !$lineHasVisibility) {
					$this->addViolation(array(
						'message' => String::insert($message, $token),
						'line' => $token['line'],
					));
				}
			}
		}
	}

	/**
	 * Will determine if the line has visibility
	 *
	 * @param  array   $tokens
	 * @param  int     $line
	 * @return boolean
	 */
	public function lineHasVisibility($tokens, $line) {
		foreach ($tokens as $token) {
			if ($token['line'] === $line && in_array($token['id'], $this->findTokens)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Will determine if the $token needs a scope token.
	 *
	 * @return boolean
	 */
	public function tokenNeedsScope($lines, $token) {
		$lineId = $token['line'] - 1;
		$content = $lines[$lineId];
		return preg_match('/^\t{2,}/', $content) === 0;
	}

	/**
	 * Will let you know if the current array of tokens has a T_CLASS
	 *
	 * @param  array   $tokens
	 * @return boolean
	 */
	public function hasClass($tokens) {
		$hasClass = false;
		foreach ($tokens as $token) {
			if ($token['id'] === T_CLASS) {
				return true;
			}
		}
		return false;
	}
}

?>