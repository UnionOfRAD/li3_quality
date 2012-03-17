<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class HasCorrectEncoding extends \li3_quality\test\Rule {

	public function apply($testable) {
		$message = "File is not encoded as UTF-8";

		if(mb_detect_encoding($testable->source(), 'UTF-8', true) != 'UTF-8') {
			$this->addViolation(compact('message'));
		}
	}

	public function enabled() {
		return function_exists('mb_detect_encoding');
	}

}

?>