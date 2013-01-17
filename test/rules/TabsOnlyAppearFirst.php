<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class TabsOnlyAppearFirst extends \li3_quality\test\Rule {

	/**
	 * Pattern will match tabs, then any non-tab character until the end of
	 * the line
	 *
	 * @var string
	 */
	public $pattern = '/^((\t+)?([^\t]+))?$/';

	/**
	 * Tokens to ignore
	 *
	 * @var array
	 */
	public $ignoreableTokens = array(
		T_ENCAPSED_AND_WHITESPACE,
		T_DOC_COMMENT,
		T_COMMENT,
		T_START_HEREDOC,
	);

	/**
	 * Will iterate over each line checking if tabs are only first
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$message = 'Tabs can only appear at the beginning of the line';
		$lines = $testable->lines();
		$tokens = $testable->tokens();
		foreach ($lines as $lineId => $line) {
			$lineNumber = $lineId + 1;
			$ignore = false;
			$key = $testable->findTokenByLine($lineNumber);
			if (isset($tokens[$key])) {
				$token = $tokens[$key];
				$ignore = in_array($token['id'], $this->ignoreableTokens);
			}
			if (!$ignore && preg_match($this->pattern, $line) === 0) {
				$token = $tokens[$testable->findTokenByLine($lineNumber)];
				$this->addViolation(array(
					'message' => $message,
					'line' => $lineNumber,
				));
			}
		}
	}

}

?>