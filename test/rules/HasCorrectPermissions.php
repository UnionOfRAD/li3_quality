<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

/**
 * Will check that that a file is not executeable.
 */
class HasCorrectPermissions extends \li3_quality\test\Rule {

	/**
	 * Checks the file is executeable and if so throws a violation.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$message = "File is executable";
		if ($this->_isExecutable($testable->config('path'))) {
			$this->addViolation(compact('message'));
		}
	}

	/**
	 * Detects wether the given file is executeable or not. Creates for easier
	 * unit tests.
	 *
	 * @param  string  $path The path to the file
	 * @return boolean
	 */
	protected function _isExecutable($path) {
		return is_executable($path);
	}

}

?>