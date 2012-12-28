<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

/**
 * 1) Echo statements should not contain any spaces after the opening, and should
 *      end with one semicolon and one space before the closing tag, i.e. <?=$post->title; ?>
 */
class HasCorrectInlineHTML extends \li3_quality\test\Rule {

	/**
	 * The pattern to match correctly formatted short tags
	 *
	 * @var string
	 */
	public $matchPattern = '/^\<\?=[^;\s]+\; \?\>$/';

	/**
	 * The pattern to match all short tags
	 *
	 * @var string
	 */
	public $findPattern = '/\<\?=.+?\?\>/';

	/**
	 * We must match one of these tokens order to scan the line
	 * The token T_OPEN_TAG_WITH_ECHO will NOT appear when short_open_tags are disabled
	 *
	 * @var array
	 */
	public $requiredTokens = array(
		T_OPEN_TAG_WITH_ECHO,
		T_INLINE_HTML,
	);

	/**
	 * Will iterate the lines until it finds one with $requiredTokens
	 * Once found it will find all short tags using $findPattern and
	 * match against them using $matchedPattern
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$message = 'Inline HTML should be in the following format: "<?=$var; ?>"';
		$lines = $testable->lines();
		$tokens = $testable->tokens();
		$lineCache = $testable->lineCache();
		$matches = array();
		foreach ($lines as $lineNumber => $line) {
			$lineTokens = isset($lineCache[$lineNumber]) ? $lineCache[$lineNumber] : array();
			if ($this->hasRequiredTokens($tokens, $lineTokens)) {
				preg_match_all($this->findPattern, $line, $matches);
				foreach ($matches as $match) {
					if (isset($match[0]) && preg_match($this->matchPattern, $match[0]) === 0) {
						$this->addViolation(array(
							'message' => $message,
							'line' => $lineNumber,
						));
					}
				}
			}
		}
	}

	/**
	 * Will let me know if the given $line has one of the $requiredTokens in it
	 *
	 * @param  array $tokens The tokens from $testable->tokens()
	 * @param  array $tokenIds The ids of tokens for the line to search for
	 * @return boolean
	 */
	public function hasRequiredTokens(array $tokens, array $tokenIds) {
		foreach ($tokenIds as $id) {
			if (in_array($tokens[$id]['id'], $this->requiredTokens)) {
				return true;
			}
		}
		return false;
	}

}

?>