<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\Inflector;

class HasCorrectClassName extends \li3_quality\test\Rule {

	/**
	 * Will iterate the tokens looking for a T_CLASS which should be
	 * in CamelCase and match the file name
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$tokens = $testable->tokens();
		$pathinfo = pathinfo($testable->config('path'));
		if ($pathinfo['extension'] !== 'php') {
			return;
		}
		foreach ($tokens as $key => $token) {
			if ($token['id'] === T_CLASS) {
				$className = $tokens[$key + 2]['content'];
				if ($className !== Inflector::camelize($className)) {
					$this->addViolation(array(
						'message' => 'Class name is not in CamelCase style',
						'line' => $token['line']
					));
				} elseif ($className !== $pathinfo['filename']) {
					$this->addViolation(array(
						'message' => 'Class name and file name should match',
						'line' => $token['line']
					));
				}
			}
		}
	}
}

?>