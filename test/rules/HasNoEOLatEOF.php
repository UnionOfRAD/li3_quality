<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class HasNoEOLatEOF extends \li3_quality\test\Rule {

	public function apply($testable) {
		$message = "EOL at EOF";
		$lines = &$testable->lines();
		$lastLine = trim($lines[count($lines)-1]);
		
		if(empty($lastLine)) {
			$this->addViolation(array(
				'message' => $message,
				'line' => count($lines)
			));
		}
	}

}

?>