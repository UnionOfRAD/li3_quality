<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\Inflector;
use li3_quality\analysis\Parser;

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
		'__isset', '__unset', '__sleep',
		'__wakeup', '__toString', '__invoke',
		'__set_state', '__clone', '__init',
	);

	/**
	 * Will iterate the tokens looking for functions validating they have the
	 * correct camelBack naming style.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$tokens = $testable->relationships();
		foreach ($tokens as $key => $token) {
			if ($token['id'] === T_FUNCTION) {
				$label = Parser::label($key, $tokens);
				$modifiers = Parser::modifiers($key, $tokens);
				$isClosure = Parser::closure($key, $tokens);
				if (in_array($label, $this->_magicMethods)) {
					continue;
				}
				if ($testable->findNext(array(T_PROTECTED), $modifiers) !== false) {
					$label = preg_replace('/^_/', '', $label);
				}
				if (!$isClosure && $label !== Inflector::camelize($label, false)) {
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