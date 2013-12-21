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
		$currLine = 1;
		$allowTabs = true;
		foreach ($tokens as $token) {
			$content = str_replace("\r\n", "\n", $token['content']);

			$isNewLine = ($token['line'] > $currLine || (
				$token['id'] === T_WHITESPACE && preg_match('/\n/', $content)
			));
			if ($isNewLine) {
				$currLine = $token['line'];
				$allowTabs = true;
			}

			if ($token['id'] !== T_WHITESPACE) {
				$allowTabs = false;
				continue;
			}

			if ($allowTabs) {
				$isInvalidTab = !preg_match('/^(\n?\t?)+ *$/', $content);
			} else {
				$isInvalidTab = preg_match('/\t/', $content);
			}
			if ($isInvalidTab) {
				$this->addViolation(array(
					'message' => $message,
					'line' => $token['line'],
				));
			}
		}
	}

}

?>