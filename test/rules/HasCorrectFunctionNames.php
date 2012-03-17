<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\Inflector;

class HasCorrectFunctionNames extends \li3_quality\test\Rule {

	protected $_magicMethods = array(
		'__construct', '__destruct', '__call',
		'__callStatic', '__get', '__set',
		'__isset', ' __unset', '__sleep',
		'__wakeup', '__toString', '__invoke',
		'__set_state', '__clone'
	);

	public function apply($testable) {
		$tokens = $testable->tokens();

		foreach($tokens as $key => $token)  {
			if($token['name'] == 'T_FUNCTION') {
				$this->_checkCamelBack($tokens[$key+2]);
			}
		}
	}

	protected function _checkCamelBack($lookahead) {
		$isMagic = in_array($lookahead['content'], $this->_magicMethods);
		if($lookahead['name'] == 'T_STRING' && !$isMagic) {
			$name = preg_replace('/^_+/', '', $lookahead['content']);
			if($name != Inflector::camelize($name, false)) {
				$this->addViolation(array(
					'message' =>  'Function "' . $name . '" is not in camelBack style',
					'line' => $lookahead['line']
				));
			}
		}
	}
}

?>