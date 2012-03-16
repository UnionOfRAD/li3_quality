<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\Inflector;

class HasCorrectVariableNames extends \li3_quality\test\Rule {

	protected $_superglobals = array(
		'$GLOBALS', '$_SERVER', '$_GET', '$_POST',
		'$_FILES', '$_COOKIE', '$_SESSION',
		'$_REQUEST', '$_ENV'
	);

	public function apply($testable) {
		$tokens = $testable->tokens();

		foreach($tokens as $token)  {
			if($token['name'] == 'T_VARIABLE' && !in_array($token['content'], $this->_superglobals)) {
				$name = preg_replace('/\$_?/', '', $token['content']);
				if($name != Inflector::camelize($name, false)) {
					$this->addViolation(array(
						'message' =>  'Variable "' . $name . '" is not in camelBack style',
						'line' => $token['line']
					));
				}
			}
		}
	}

}

?>