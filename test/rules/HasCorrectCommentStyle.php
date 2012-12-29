<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use li3_quality\analysis\Parser;

class HasCorrectCommentStyle extends \li3_quality\test\Rule {

	/**
	 * Will iterate tokens looking for comments and if found will determine the regex
	 * to test the comment against.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$tokens = $testable->tokens();

		$comments = $testable->findAll(array(T_COMMENT));
		foreach ($comments as $tokenId) {
			$token = $tokens[$tokenId];
			$parentId = $tokens[$tokenId]['parent'];
			if ($parentId === -1 || $tokens[$parentId]['id'] !== T_FUNCTION) {
				$this->addViolation(array(
					'message' => 'Inline comments should never appear.',
					'line' => $token['line'],
				));
			} elseif (preg_match('/^test/', Parser::label($parentId, $tokens)) === 0) {
				$this->addViolation(array(
					'message' => 'Inline comments should only appear in testing methods.',
					'line' => $token['line'],
				));
			}
		}

	}

}

?>