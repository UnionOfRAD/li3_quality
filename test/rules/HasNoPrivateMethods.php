<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class HasNoPrivateMethods extends \li3_quality\test\Rule {

	public function apply($testable) {
		$tokens = $testable->tokens();

		foreach($tokens as $key => $token)  {
			if($token['name'] == 'T_PRIVATE') {
				$this->addViolation(array(
					'message' =>  'Private method found',
					'line' => $token['line']
				));
			}
		}
	}

}

?>