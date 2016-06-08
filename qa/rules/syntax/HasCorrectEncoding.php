<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa\rules\syntax;

class HasCorrectEncoding extends \li3_quality\qa\Rule {

	public function apply($testable, array $config = array()) {
		$message = "File is not encoded as UTF-8";

		$encoding = mb_detect_encoding($testable->source(), 'UTF-8', true);
		if ($encoding !== 'UTF-8') {
			$this->addViolation(compact('message'));
		}
	}

	public function enabled($testable, array $config = array()) {
		return function_exists('mb_detect_encoding');
	}

}

?>