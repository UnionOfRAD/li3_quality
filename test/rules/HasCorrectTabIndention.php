<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;

class HasCorrectTabIndention extends \li3_quality\test\Rule {

	/**
	 * The forceOneMoreLine applies to the next line
	 * The forceOneLessLine applies to the current line
	 * @var array
	 */
	public $patterns = array(
		'forceOneMoreLine'    => '/({|\()$/',
		'forceOneLessLine'    => '/^\s*[}\)]+(;$|)?/',
		'tempOneMoreLine'     => '/\.$/',
		'optionalOneLessLine' => '/[\(}],$/'
	);

	public $commentTokens = array(
		T_COMMENT,
		T_DOC_COMMENT,
	);

	/**
	 * The error message when they have too many tabs.
	 * @var string
	 */
	public $positiveMessage = 'This line has an extra {:extraCount} tabs.';

	/**
	 * The error message when they don't have enough tabs.
	 * @var string
	 */
	public $negativeMessage = 'This line has {:extraCount} too many tabs.';

	/**
	 * Will iterate the lines looking for $patterns while keeping track of how many tabs
	 * the current line should have.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$followerCount = 0;
		$lines = $testable->lines();
		$tokens = $testable->tokens();
		$tempOneMoreLine = false;
		$optionalOneLessLine = false;
		foreach ($lines as $lineIndex => $line) {
			if (!$this->_shouldIgnoreLine($lineIndex, $lines, $tokens)) {
				$leaderCount = $this->_beginningTabCount($line);
				if (preg_match($this->patterns['forceOneLessLine'], $line) === 1) {
					$followerCount--;
				}
				$leaderShouldBe = array($followerCount);
				if ($optionalOneLessLine) {
					$optionalOneLessLine = false;
					$leaderShouldBe[] = $followerCount - 1;
				}
				if (!in_array($leaderCount, $leaderShouldBe)) {
					$extraCount = $leaderCount - $followerCount;
					$message = ($extraCount > 0) ? $this->positiveMessage : $this->negativeMessage;
					$this->addViolation(array(
						'message' => String::insert($message, array(
							'extraCount' => abs($extraCount),
						)),
						'line' => $lineIndex + 1,
					));
				}
				if ($tempOneMoreLine) {
					$tempOneMoreLine = false;
					$followerCount--;
				}
				if (preg_match($this->patterns['forceOneMoreLine'], $line) === 1) {
					$followerCount++;
				} elseif (preg_match($this->patterns['tempOneMoreLine'], $line) === 1) {
					$tempOneMoreLine = true;
					$followerCount++;
				} elseif (preg_match($this->patterns['optionalOneLessLine'], $line) === 1) {
					$optionalOneLessLine = true;
				}
			}
		}
	}

	/**
	 * Will determine how many tabs are at the beginning of a line.
	 *
	 * @param  string $line The line to look at
	 * @return int
	 */
	protected function _beginningTabCount($line) {
		preg_match_all('/^(\t+)[^\t]/', $line, $matches);
		if (isset($matches[1][0])) {
			return strlen($matches[1][0]);
		}
		return 0;
	}

	/**
	 * Will determine if the line is ignoreable.
	 * Currently the line is ignoreable if:
	 *  * Empty Line
	 *  * In docblocks
	 *  * In heredocs
	 *
	 * @param  int    $lineIndex The index the current line is on
	 * @param  array  $lines     The array of lines
	 * @param  array  $tokens    The array of tokens
	 * @return bool
	 */
	protected function _shouldIgnoreLine($lineIndex, $lines, $tokens) {
		$start = $this->_findTokenByLine($lineIndex + 1, $tokens);
		if (!isset($tokens[$start])) {
			return false;
		}
		if (empty($lines[$lineIndex]) || in_array($tokens[$start]['id'], $this->commentTokens)) {
			return true;
		}
		for ($i = $start, $total = count($tokens); $i < $total;$i++) {
			if (isset($tokens[$i]['id'])) {
				if ($tokens[$i]['id'] === T_END_HEREDOC) {
					return true;
				} else if ($tokens[$i]['id'] === T_START_HEREDOC) {
					return false;
				}
			}
		}
		return false;
	}

}

?>