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
		$classes = $testable->findAll(array(T_CLASS));
		foreach ($classes as $classId) {
			$children = $tokens[$classId]['children'];
			$members = $testable->findAll($this->inspectableTokens, $children);
			foreach ($members as $member) {
				$token = $tokens[$member];
				$tokenChildren = $token['children'];
				$visibility = $testable->findNext($this->findTokens, $tokenChildren);
				if ($visibility === false) {
					$this->addViolation(array(
						'message' => String::insert($message, $token),
						'line' => $token['line'],
					));
				}
			}
		}
	}

}

?>