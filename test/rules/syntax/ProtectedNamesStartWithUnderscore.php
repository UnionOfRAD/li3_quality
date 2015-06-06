<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules\syntax;

use lithium\util\String;
use li3_quality\analysis\Parser;

class ProtectedNamesStartWithUnderscore extends \li3_quality\test\Rule {

	/**
	 * List of exceptions (in regex format). If a class name matches one of this
	 * exception list regex, the rule will produce a warnings instead of errors.
	 */
	protected $_exceptions = array(
		'Exception$'
	);

	/**
	 * Will iterate the tokens looking for protected methods and variables, once
	 * found it will validate the name of it's parent starts with an underscore.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$message = 'Protected method {:name} must start with an underscore';
		$tokens = $testable->tokens();
		$filtered = $testable->findAll(array(T_PROTECTED));

		foreach ($filtered as $tokenId) {
			$token = $tokens[$tokenId];
			$parent = $testable->findNext(array(T_FUNCTION, T_VARIABLE), $tokenId);
			$parentLabel = Parser::label($parent, $tokens);
			if (substr($parentLabel, 0, 1) !== '_') {
				$classTokenId = $testable->findNext(array(T_STRING), $token['parent']);
				$classname = $tokens[$classTokenId]['content'];
				$params = array(
					'message' => String::insert($message, array(
						'name' => $parentLabel,
					)),
					'line' => $token['line']
				);
				if ($this->_strictMode($classname)) {
					$this->addViolation($params);
				} else {
					$this->addWarning($params);
				}
			}
		}
	}

	/**
	 * Will iterate over exceptions regex to see if the rule need to be strictly applied.
	 *
	 * @param  string $classname A class name
	 * @return boolean
	 */
	protected function _strictMode($classname) {
		foreach ($this->_exceptions as $regex) {
			if (preg_match("/{$regex}/", $classname)) {
				return false;
			}
		}
		return true;
	}

}

?>