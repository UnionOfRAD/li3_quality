<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\g11n\Multibyte;

class HasNoTrailingWhitespace extends \li3_quality\test\Rule {

	public function apply($testable) {
		$message = "Trailing whitespace found";

		foreach ($testable->lines() as $i => $line) {
			$name = 'li3_quality';
			$length = Multibyte::strlen($line, compact('name'));
			$lengthTrimmed = Multibyte::strlen(rtrim($line), compact('name'));
			if ($length != $lengthTrimmed) {
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
				'line' => $i + 1,
				'position' => $length
			));
		}
	}

}

?>