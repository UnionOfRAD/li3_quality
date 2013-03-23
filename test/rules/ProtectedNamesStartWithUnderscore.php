<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;
use li3_quality\analysis\Parser;

/**
 * Will determine if all protected names (T_FUNCTION and T_VARIABLE) have names
 * that start with an underscore '_' character.
 */
class ProtectedNamesStartWithUnderscore extends \li3_quality\test\Rule {

	/**
	 * Will iterate the tokens looking for protected methods and variables, once
	 * found it will validate the name of it's parent starts with an underscore.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$message = 'Protected method {:name} must start with an underscore';
		$tokens = $testable->tokens();
		$filtered = $testable->findAll(array(T_PROTECTED));

		foreach ($filtered as $tokenId) {
			$token = $tokens[$tokenId];
			$parent = $testable->findNext(array(T_FUNCTION, T_VARIABLE), $tokenId);
			$parentLabel = Parser::label($parent, $tokens);
			if (substr($parentLabel, 0, 1) !== '_') {
				$this->addViolation(array(
					'message' => String::insert($message, array(
						'name' => $parentLabel,
					)),
					'line' => $token['line']
				));
			}
		}
	}

}

?>