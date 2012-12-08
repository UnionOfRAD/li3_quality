<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class HasCorrectPermissions extends \li3_quality\test\Rule {

	public function apply($testable) {
		$message = "File is executable";
		if (is_executable($testable->config('path'))) {
			$this->addViolation(compact('message'));
		}
	}

}

?>