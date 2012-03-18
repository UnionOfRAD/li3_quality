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
		'T_ENDDECLARE', 'T_ENDFOR', 'T_ENDFOREACH',
		'T_ENDIF', 'T_ENDSWITCH', 'T_ENDWHILE',
		'T_PRINT', 'T_GOTO', 'T_EVAL',
		'T_GLOBAL', 'T_VAR', 'T_AT'
	);

	public function apply($testable) {
		$tokens = $testable->tokens();
		foreach($tokens as $token) {
			if(in_array($token['name'], $this->_forbidden)) {
				$token = strtolower(substr($token['name'], 2));
				$this->addViolation(array(
					'message' => 'Forbidden "' . $token . '" statement found',
					'line' => $token['line']
				));
			}

			if($token['name'] === 'T_STRING' && $token['content'] === 'var_dump') {
				$this->addViolation(array(
					'message' => 'Forbidden "var_dump" statement found',
					'line' => $token['line']
				));
			}
		}
	}
}

?>