<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

/**
 * Validates that there are no incorrectly used constructs such as eval or short
 * hand syntax.
 */
class HasNoForbiddenStatements extends \li3_quality\test\Rule {

	/**
	 * The list of items to search for
	 *
	 * @todo  apply this in the config file
	 * @var   array
	 */
	protected $_forbidden = array(
		T_ENDDECLARE => 'enddeclare',
		T_ENDFOR => 'endfor',
		T_ENDFOREACH => 'endforeach',
		T_ENDIF => 'endif',
		T_ENDSWITCH => 'endswitch',
		T_ENDWHILE => 'endwhile',
		T_PRINT => 'print',
		T_GOTO => 'goto',
		T_EVAL => 'eval',
		T_GLOBAL => 'global',
		T_VAR => 'var'
	);

	/**
	 * Iterates searching for the given tokens.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$tokens = $testable->tokens();
		$filtered = array_merge(array_keys($this->_forbidden), array(T_STRING));
		$filtered = $testable->findAll($filtered);

		foreach ($filtered as $id) {
			$token = $tokens[$id];
			if (isset($this->_forbidden[$token['id']])) {
				$tokenName = $this->_forbidden[$token['id']];
				$this->addViolation(array(
					'message' => "Forbidden {$tokenName} statement found",
					'line' => $token['line']
				));
			}

			if ($token['id'] === T_STRING && $token['content'] === 'var_dump') {
				$this->addViolation(array(
					'message' => 'Forbidden "var_dump" statement found',
					'line' => $token['line']
				));
			}
		}
	}
}

?>