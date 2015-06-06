<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules\syntax;

class HasTabsAsIndentation extends \li3_quality\test\Rule {

	/**
	 * Tokens to ignore
	 *
	 * @var array
	 */
	public $ignoreableTokens = array(
		T_ENCAPSED_AND_WHITESPACE,
	);

	public function apply($testable, array $config = array()) {
		$message = "Uses spaces instead of tabs";
		$lines = $testable->lines();
		$tokens = $testable->tokens();

		foreach ($lines as $number => $line) {
			$lineNumber = $number + 1;
			$ignore = false;
			$key = $testable->findTokenByLine($lineNumber);
			if (isset($tokens[$key])) {
				$token = $tokens[$key];
				$ignore = in_array($token['id'], $this->ignoreableTokens);
			}
			if (!$ignore && preg_match('/^ +[^*]/', $line)) {
				$this->addViolation(array(
					'message' => $message,
					'line' => $number + 1
				));
			}
		}
	}

}

?>