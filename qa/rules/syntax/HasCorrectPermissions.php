<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa\rules\syntax;

class HasCorrectPermissions extends \li3_quality\qa\Rule {

	public function apply($testable, array $config = array()) {
		$message = "File is executable";
		if ($this->_isExecutable($testable->config('path'))) {
			$this->addViolation(compact('message'));
		}
	}

	protected function _isExecutable($path) {
		return is_executable($path);
	}

}

?>