<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class HasNoForbiddenStatements extends \li3_quality\test\Rule {

	protected $_forbidden = array(
		'T_ENDDECLARE' => 'enddeclare',
		'T_ENDFOR' => 'endfor',
		'T_ENDFOREACH' => 'endforeach',
		'T_ENDIF' => 'endif',
		'T_ENDSWITCH' => 'endswitch',
		'T_ENDWHILE' => 'endwhile',
		'T_PRINT' => 'print',
		'T_GOTO' => 'goto',
		'T_EVAL' => 'eval',
		'T_GLOBAL' => 'global',
		'T_VAR' => 'var'
	);

	public function apply($testable) {
		$tokens = $testable->tokens();
		foreach ($tokens as $token) {
			if (isset($this->_forbidden[$token['name']])) {
				$tokenName = $this->_forbidden[$token['name']];
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