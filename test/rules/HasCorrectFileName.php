<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\Inflector;

class HasCorrectFileName extends \li3_quality\test\Rule {

	public function apply($testable) {
		$message = "File is not in CamelCase style";

		$pathinfo = pathinfo($testable->config('path'));

		if($pathinfo['extension'] != 'php') {
			return;
		}

		if($pathinfo['filename'] != Inflector::camelize($pathinfo['filename'])) {
			$this->addViolation(array('message' => $message));
		}
	}

}

?>