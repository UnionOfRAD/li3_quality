<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\g11n\Multibyte;

/**
 * Validates there is no trailing whitespace on a line.
 */
class HasNoTrailingWhitespace extends \li3_quality\test\Rule {

	/**
	 * Iterates lines and testing it against a regex to determine it's success.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$message = "Trailing whitespace found";
		$lines = $testable->lines();

		foreach ($lines as $i => $line) {
			$name = 'li3_quality';
			$length = Multibyte::strlen($line, compact('name'));
			$lengthTrimmed = Multibyte::strlen(rtrim($line), compact('name'));
			if ($length !== $lengthTrimmed) {
				$this->addViolation(array(
					'message' => $message,
					'line' => $i + 1,
					'position' => $length
				));
			}
		}
		if (empty($line)) {
			$this->addViolation(array(
				'message' => $message,
				'line' => count($lines)
			));
		}
	}

}

?>