<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

/**
 * This rule throws violations when private methods/variables are used in your
 * code.
 */
class HasNoPrivateMethods extends \li3_quality\test\Rule {

	/**
	 * Iterates all tokens simply looking for the private token, once found
	 * throws a violation.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$tokens = $testable->tokens();
		$filtered = $testable->findAll(array(T_PRIVATE));

		foreach ($filtered as $key) {
			$token = $tokens[$key];
			$this->addViolation(array(
				'message' => 'Private method found',
				'line' => $token['line']
			));
		}
	}

}

?>