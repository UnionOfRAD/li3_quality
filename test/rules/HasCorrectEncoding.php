<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

/**
 * Ensures the document has the correct UTF-8 encoding.
 */
class HasCorrectEncoding extends \li3_quality\test\Rule {

	/**
	 * Will do a detection on the entire source for UTF-8
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$message = "File is not encoded as UTF-8";

		$encoding = mb_detect_encoding($testable->source(), 'UTF-8', true);
		if ($encoding !== 'UTF-8') {
			$this->addViolation(compact('message'));
		}
	}

	/**
	 * Disables this rule if mb_detect_encoding is not installed.
	 *
	 * @return bool
	 */
	public function enabled() {
		return function_exists('mb_detect_encoding');
	}

}

?>