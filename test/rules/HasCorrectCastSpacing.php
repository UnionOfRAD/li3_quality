<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

/**
 * 1) Casts should use long names, and only contain exactly one space between
 *      the cast clause and the expression
 */
class HasCorrectCastSpacing extends \li3_quality\test\Rule {

	/**
	 * The pattern to match correctly formatted casts
	 *
	 * @var string
	 */
	public $pattern = '/^\([a-z]+\) [^ ]/';

	/**
	 * A list of the cast tokens
	 *
	 * @var array
	 */
	public $tokens = array(
		T_ARRAY_CAST,
		T_BOOL_CAST,
		T_DOUBLE_CAST,
		T_INT_CAST,
		T_OBJECT_CAST,
		T_STRING_CAST,
		T_UNSET_CAST,
	);

	/**
	 * Will iterate tokens looking for cast tokens then apply a regex over them
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$message = 'Casting should be done in the following format: "(array) $object;"';
		$tokens = $testable->tokens();
		foreach ($tokens as $id => $token) {
			if (in_array($token['id'], $this->tokens)) {
				$html = '';
				foreach (array_slice($tokens, $id, 3) as $t) {
					$html .= $t['content'];
				}
				if (preg_match($this->pattern, $html) === 0) {
					$this->addViolation(array(
						'message' => $message,
						'line' => $token['line'],
					));
				}
			}
		}
	}

}

?>