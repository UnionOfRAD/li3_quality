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

	/**
	 * The rule can ignore these methods.
	 *
	 * @link http://php.net/manual/en/language.oop5.magic.php
	 * @var array
	 */
	protected $_magicMethods = array(
		'__construct', '__destruct', '__call',
		'__callStatic', '__get', '__set',
		'__isset', ' __unset', '__sleep',
		'__wakeup', '__toString', '__invoke',
		'__set_state', '__clone'
	);

	/**
	 * Will iterate the tokens looking for functions validating they have the
	 * correct camelBack naming style.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$tokens = $testable->tokens();
		foreach ($tokens as $key => $token) {
			if ($token['id'] === T_FUNCTION) {
				$label = $token['label'];
				if (in_array($label, $this->_magicMethods)) {
					continue;
				}
				if ($testable->findNext(array(T_PROTECTED), $token['children']) !== false) {
					$label = preg_replace('/^_/', '', $label);
				}
				if ($label !== Inflector::camelize($label, false)) {
					$this->addViolation(array(
						'message' => 'Function "' . $label . '" is not in camelBack style',
						'line' => $tokens[$token['parent']]['line'],
					));
				}
			}
		}
	}

}

?>