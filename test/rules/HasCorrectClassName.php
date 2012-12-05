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

	public function apply($testable) {
		$tokens = $testable->tokens();

		foreach ($tokens as $key => $token) {
			if ($token['name'] == 'T_CLASS') {
				$className = $tokens[$key+2]['content'];
				if ($className != Inflector::camelize($className)) {
					$this->addViolation(array(
						'message' =>  'Class name is not in CamelCase style',
						'line' => $token['line']
					));
				}
			}
		}
	}
}

?>