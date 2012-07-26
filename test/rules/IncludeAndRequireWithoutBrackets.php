<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class IncludeAndRequireWithoutBrackets extends \li3_quality\test\Rule {

	protected $_inspectableTokens = array(
		'T_INCLUDE'      => 'include statement should not use brackets',
		'T_INCLUDE_ONCE' => 'include_once statement should not use brackets',
		'T_REQUIRE'      => 'require statement should not use brackets',
		'T_REQUIRE_ONCE' => 'require_once statement should not use brackets'
	);

	public function apply($testable) {
		$tokens = $testable->tokens();

		foreach ($tokens as $key => $token) {
			if (in_array($token['name'], array_keys($this->_inspectableTokens))) {
				if ($tokens[$key+1]['content'] == '(') {
					$this->addViolation(array(
						'message' => $this->_inspectableTokens[$token['name']],
						'line' => $token['line']
					));
				}
			}
		}
	}
}

?>