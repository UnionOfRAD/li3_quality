<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa\rules\syntax;

class MultipleBlankLines extends \li3_quality\qa\Rule {

	/**
	 * Tokens to ignore
	 *
	 * @var array
	 */
	public $ignoreableTokens = array(
		T_ENCAPSED_AND_WHITESPACE,
		T_START_HEREDOC,
	);

	/**
	 * Will iterate over each line checking for blank lines
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$lines = $testable->lines();
		$tokens = $testable->tokens();
		$lastBlankLineId = -2;

		foreach ($lines as $lineId => $line) {
			$lineNumber = $lineId + 1;
			$ignore = false;
			$key = $testable->findTokenByLine($lineNumber);

			if (isset($tokens[$key])) {
				$token = $tokens[$key];
				$ignore = in_array($token['id'], $this->ignoreableTokens, true);
			}
			if (!$ignore && preg_match('/^$/', $line) === 1) {
				if ($lastBlankLineId + 1 === $lineId) {
					$this->addViolation(array(
						'message' => 'Multiple blank lines.',
						'line' => $lineNumber,
					));
				}
				$lastBlankLineId = $lineId;
			}
		}
	}

}

?>