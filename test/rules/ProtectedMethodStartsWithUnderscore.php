<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;


class ProtectedMethodStartsWithUnderscore extends \li3_quality\test\Rule {

	public function apply($testable) {
		$tokens = $testable->tokens();

		foreach ($tokens as $position => $token) {
			if ($token['name'] == 'T_PROTECTED') {
				$lookaheadTokens = array_slice($tokens, $position+1, 10, true);
				$result = $this->_lookahead($lookaheadTokens);
				if ($result['found'] === true && $result['match'] === false) {
					$this->addViolation(array(
						'message' =>  'Protected Method "' . $result['name'] . '" does not ' .
									  'start with "_"',
						'line' => $token['line']
					));
				}
			}
		}
	}

	protected function _lookahead($tokenSlice) {
		$hasFunctionToken = false;
		foreach ($tokenSlice as $token) {
			if ($token['name'] == 'T_VARIABLE') {
				break;
			}
			if ($token['name'] == 'T_WHITESPACE' || $token['name'] == 'T_STATIC') {
				continue;
			}

			if ($token['name'] == 'T_FUNCTION') {
				$hasFunctionToken = true;
				continue;
			}

			if ($token['name'] == 'T_STRING' && $hasFunctionToken) {
				if (strpos($token['content'], '_') === false) {
					return array('found' => true, 'match' => false, 'name' => $token['content']);
				} else {
					return array('found' => true, 'match' => true, 'name' => $token['content']);
				}
			}
		}
		return array('found' => false);
	}

}

?>