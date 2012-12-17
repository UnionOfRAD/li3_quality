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

	public $config = array(
		'maxLimit' => 100,
		'softLimit' => 80,
		'tabWidth' => 3,
	);

	public function apply($testable) {
		extract($this->config);
		foreach ($testable->lines() as $i => $line) {
			$tabBounty = substr_count($line, "\t") * $tabWidth;
			$strlen = Multibyte::strlen($line, array('name' => 'li3_quality'));
			$totalLength = ($length = $tabBounty + $strlen);
			if ($totalLength > $maxLimit) {
				$this->addViolation(array(
					'message' => "Maximum line length of {$maxLimit} exceeded",
					'line' => $i + 1,
					'position' => $length
				));
			} elseif ($totalLength >= $softLimit) {
				$this->addWarning(array(
					'message' => "Soft line length of {$softLimit} exceeded",
					'line' => $i + 1,
					'position' => $length
				));
			}
		}
	}

}

?>