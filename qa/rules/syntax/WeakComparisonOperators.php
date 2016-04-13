<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa\rules\syntax;

use lithium\util\Text;

class WeakComparisonOperators extends \li3_quality\qa\Rule {

	/**
	 * Tokens to inspect
	 *
	 * @var array
	 */
	public $inspectableTokens = array(
		T_IS_EQUAL => '===',
		T_IS_NOT_EQUAL => '!==',
	);

	/**
	 * Will iterate over each line checking if any weak comparison operators
	 * are used within the code.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$tokens = $testable->tokens();
		$message = 'Weak comparison operator {:key} used, try {:value} instead';
		$filtered = $testable->findAll(array_keys($this->inspectableTokens));

		foreach ($filtered as $id) {
			$token = $tokens[$id];
			$this->addWarning(array(
				'message' => Text::insert($message, array(
					'key' => token_name($token['id']),
					'value' => $this->inspectableTokens[$token['id']],
				)),
				'line' => $token['line'],
			));
		}
	}

}

?>