<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class DoesntExceedMaxLineLength extends \li3_quality\test\Rule {

	public function apply($testable) {
		$maxLength = 100;
		$tabWidth  = 3;
		$message = "Maximum line length " . $maxLength . " exceeded";

		$strlenFunction = 'strlen';
		if(function_exists('mb_strlen')) {
			$strlenFunction = 'mb_strlen';
		}

		foreach($testable->lines() as $i => $line) {
			$tabBounty = substr_count($line, "\t") * $tabWidth;
			if(($length = $tabBounty + $strlenFunction($line)) > $maxLength) {
				$this->addViolation(array(
					'message' => $message,
					'line' => $i+1,
					'position' => $length
				));
			}
		}
	}

}

?>