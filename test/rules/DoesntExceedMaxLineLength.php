<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\g11n\Multibyte;

class DoesntExceedMaxLineLength extends \li3_quality\test\Rule {

	public function apply($testable) {
		$maxLength = 100;
		$tabWidth = 3;
		$message = "Maximum line length of \"" . $maxLength . "\" exceeded";

		foreach ($testable->lines() as $i => $line) {
			$tabBounty = substr_count($line, "\t") * $tabWidth;
			$strlen = Multibyte::strlen($line, array('name' => 'li3_quality'));
			if (($length = $tabBounty + $strlen) > $maxLength) {
				$this->addViolation(array(
					'message' => $message,
					'line' => $i + 1,
					'position' => $length
				));
			}
		}
	}

}

?>