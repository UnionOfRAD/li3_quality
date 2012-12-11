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

	/**
	 * Tokens that require visibility
	 *
	 * @var array
	 */
	public $inspectableTokens = array(
		T_FUNCTION,
		T_VARIABLE,
	);

	/**
	 * Visibility tokens
	 *
	 * @var array
	 */
	public $findTokens = array(
		T_PUBLIC,
		T_PROTECTED,
		T_PRIVATE,
	);

	/**
	 * Will iterate all the tokens looking for tokens in inspectableTokens
	 * The token needs an access modifier if it is a T_FUNCTION or T_VARIABLE
	 * and is in the first level of T_CLASS. This prevents functions and variables
	 * inside methods and outside classes to register violations.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$message = '{:name} has no declared visibility.';
		$tokens = $testable->tokens();
		$openBrackets = 0;
		$insideClass = false;
		$foundFirstClassBracket = false;
		$foundClassOnBracket = -1;
		foreach ($tokens as $tokenId => $token) {
			$openBrackets += substr_count($token['content'], '{');
			$openBrackets -= substr_count($token['content'], '}');
			if ($token['id'] === T_CLASS) {
				$foundClassOnBracket = $openBrackets;
				$insideClass = true;
			} elseif ($insideClass) {
				$isInspectableToken = $this->isInspectableToken($token, $tokens);
				$isInspectableLine = $openBrackets === $foundClassOnBracket + 1;
				$tokenHasVisibility = false;
				if ($isInspectableLine && $isInspectableToken) {
					$tokenHasVisibility = $this->tokenHasVisibility($tokenId, $tokens);
				}
				if (!$foundFirstClassBracket && $openBrackets >= $foundClassOnBracket + 1) {
					$foundFirstClassBracket = true;
				}
				if ($isInspectableLine && $isInspectableToken && !$tokenHasVisibility) {
					$this->addViolation(array(
						'message' => String::insert($message, $token),
						'line' => $token['line'],
					));
				} elseif ($foundFirstClassBracket && $openBrackets <= 0) {
					$insideClass = $foundFirstClassBracket = false;
				}
			}
		}
	}

	/**
	 * Will determine if the token has visibility
	 *
	 * @param  int     $tokenId
	 * @param  array   $tokens
	 * @return boolean
	 */
	public function tokenHasVisibility($tokenId, $tokens) {
		$tokenStart = $tokenId - 5;
		$tokenStart = ($tokenStart < 0) ? 0 : $tokenStart;
		$length = $tokenId - $tokenStart;
		$searchableTokens = array_reverse(array_slice($tokens, $tokenStart, $length));
		foreach ($searchableTokens as $token) {
			if (in_array($token['id'], $this->findTokens)) {
				return true;
			} elseif (in_array($token['id'], $this->inspectableTokens)) {
				return false;
			}
		}
		return false;
	}

	/**
	 * Will detect if the current tokens is something we should inspect.
	 *
	 * @param  array   $token
	 * @param  array   $tokens
	 * @return boolean
	 */
	public function isInspectableToken($token, $tokens) {
		if (!in_array($token['id'], $this->inspectableTokens)) {
			return false;
		}
		if ($token['id'] === T_VARIABLE) {
			$lineTokens = array();
			foreach ($tokens as $t) {
				if ($t['line'] === $token['line'] && $t['id'] === T_FUNCTION) {
					return false;
				}
			}
		}
		return true;
	}

}

?>