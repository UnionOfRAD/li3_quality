<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\Inflector;

/**
 * Ensures all variables are in the correct camelBack style.
 */
class HasCorrectVariableNames extends \li3_quality\test\Rule {

	/**
	 * Standard php variables we should ignore.
	 * @var array
	 */
	protected $_superglobals = array(
		'$GLOBALS'  => true,
		'$_SERVER'  => true,
		'$_GET'     => true,
		'$_POST'    => true,
		'$_FILES'   => true,
		'$_COOKIE'  => true,
		'$_SESSION' => true,
		'$_REQUEST' => true,
		'$_ENV'     => true
	);

	/**
	 * Iterates tokens looking for for variables and checking their content.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$tokens = $testable->tokens();
		$filtered = $testable->findAll(array(T_VARIABLE));

		foreach ($filtered as $id) {
			$token = $tokens[$id];
			$isntSuperGlobal = !isset($this->_superglobals[$token['content']]);
			if ($isntSuperGlobal) {
				$name = preg_replace('/(\$_?|_+$)/', '', $token['content']);
				if ($name !== Inflector::camelize($name, false)) {
					$this->addViolation(array(
						'message' => "Variable {$name} is not camelBack style",
						'line' => $token['line']
					));
				}
			}
		}
	}

}

?>