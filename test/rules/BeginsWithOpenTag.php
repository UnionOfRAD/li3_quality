<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\analysis\Docblock;

/**
 * Validates php files begin with the correct opening php tag.
 */
class BeginsWithOpenTag extends \li3_quality\test\Rule {

	/**
	 * Checks the first line for the opening php tag
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$message = "File does not begin with <?php";
		$lines = $testable->lines();

		if ($lines[0] !== "<?php") {
			$this->addViolation(array(
				'message' => $message,
				'line' => 1
			));
		}
	}

}

?>