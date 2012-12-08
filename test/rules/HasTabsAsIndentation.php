<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class HasTabsAsIndentation extends \li3_quality\test\Rule {

	public function apply($testable) {
		$message = "Uses spaces instead of tabs";
		$lines = $testable->lines();

		foreach ($lines as $number => $line) {
			if (preg_match('/^ +[^*]/', $line)) {
				$this->addViolation(array(
					'message' => $message,
					'line' => $number + 1
				));
			}
		}
	}

}

?>