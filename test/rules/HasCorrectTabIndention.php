<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;

/**
 * Tab indention is a tricky subject. Often the next line is based on the
 * relative indention of the line above it, and if that has a violation it can
 * often trigger a violation chain because of, what should be, a single
 * violation. It's also possible to not know when the indention broke and we
 * trigger an erorr at the end of the file. This is similar behavior to PHP when
 * you forget a bracket and you get the syntax error at the end of the file.
 */
class HasCorrectTabIndention extends \li3_quality\test\Rule {

	/**
	 * The error message when they have too many tabs.
	 *
	 * @var string
	 */
	public $positiveMessage = 'This line has an extra {:extraCount} tabs.';

	/**
	 * The error message when they don't have enough tabs.
	 *
	 * @var string
	 */
	public $negativeMessage = 'This line is missing {:extraCount} tabs.';

	/**
	 * This method is foobared.
	 *
	 * @return bool
	 */
	public function enabled() {
		return false;
	}

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
		$switchQueue = array();
		foreach ($lines as $lineIndex => $line) {
			if (!$this->_shouldIgnoreLine($lineIndex, $lines, $tokens)) {
				$leaderCount = $this->_beginningTabCount($lineIndex, $lines, $tokens);
				$relTab = $this->_lineHasRelativeTab($testable, $lineIndex, $lines, $tokens);
				$followerCount += $relTab;
				$leaderShouldBe = $followerCount;

				if ($leaderCount !== $leaderShouldBe) {
					if (($queueCount = count($switchQueue)) > 0) {
						$hasCount = $leaderCount === $switchQueue[$queueCount - 1];
						$hasContent = preg_match('/^\s*}\s*$/', $line) === 1;
						if ($hasCount && $hasContent) {
							array_pop($switchQueue);
							$followerCount--;
							continue;
						}
					}
					$extraCount = $leaderCount - $leaderShouldBe;
					$message = ($extraCount > 0) ? 'positiveMessage' : 'negativeMessage';
					$this->addViolation(array(
						'message' => String::insert($this->$message, array(
							'extraCount' => abs($extraCount),
						)),
						'line' => $lineIndex + 1,
					));
					$followerCount = $leaderCount;
				}

				if ($tempOneMoreLine) {
					$tempOneMoreLine = false;
					$followerCount--;
				}

				if ($this->_nextLineHasTab($lineIndex, $lines, $tokens)) {
					$followerCount++;
				} elseif ($this->_nextLineHasTempTab($lineIndex, $lines, $tokens)) {
					$tempOneMoreLine = true;
					$followerCount++;
				}

				if ($testable->lineHasToken($lineIndex + 1, array(T_SWITCH))) {
					$switchQueue[] = $followerCount - 1;
					$followerCount++;
				}
			}
		}
		if (($queueCount = count($switchQueue)) > 0) {
			$this->addViolation(array(
				'message' => 'Incorrect tab indention',
				'line' => count($lines),
			));
		}
	}

	/**
	 * Will determine if the current line has 1 more/less tab than expected.
	 *
	 * @param  int   $lineIndex
	 * @param  array $lines
	 * @param  array $tokens
	 * @return int
	 */
	protected function _lineHasRelativeTab($testable, $lineIndex, &$lines, &$tokens) {
		$hasEndingLine = preg_match('/^\s*[}\)]+(;$|)?/', $lines[$lineIndex]) === 1;
		$hasCaseDefault = $testable->lineHasToken($lineIndex + 1, array(
			T_CASE,
			T_DEFAULT
		));
		$hasEndingToken = $testable->linehasToken($lineIndex + 1, array(
			T_ENDDECLARE,
			T_ENDFOR,
			T_ENDFOREACH,
			T_ENDIF,
			T_ENDSWITCH,
			T_ENDWHILE,
		));
		if ($hasEndingLine || $hasCaseDefault || $hasEndingToken) {
			return -1;
		}
		return 0;
	}

	/**
	 * Will determine if the next line should be on a new tab.
	 *
	 * @param  int   $lineIndex
	 * @param  array $lines
	 * @param  array $tokens
	 * @return bool
	 */
	protected function _nextLineHasTab($lineIndex, &$lines, &$tokens) {
		return preg_match('/((({|\(|\:)$)|(^\s+(case|default)(.*):))$/', $lines[$lineIndex]) === 1;
	}

	/**
	 * Will determine if the next line should be on a new tab, but following
	 * lines should not.
	 *
	 * @param  int   $lineIndex
	 * @param  array $lines
	 * @param  array $tokens
	 * @return bool
	 */
	protected function _nextLineHasTempTab($lineIndex, &$lines, &$tokens) {
		return preg_match('/\.$/', $lines[$lineIndex]) === 1;
	}

	/**
	 * Will determine how many tabs are at the beginning of a line.
	 *
	 * @param  int    $lineIndex The index the current line is on
	 * @param  array  $lines     The array of lines
	 * @param  array  $tokens    The array of tokens
	 * @return int
	 */
	protected function _beginningTabCount($lineIndex, &$lines, &$tokens) {
		$line = $lines[$lineIndex];
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
	protected function _shouldIgnoreLine($lineIndex, &$lines, &$tokens) {
		$start = $this->_findTokenByLine($lineIndex + 1, $tokens);
		if (!isset($tokens[$start])) {
			return false;
		}
		$isComment = in_array($tokens[$start]['id'], array(
			T_COMMENT,
			T_DOC_COMMENT,
		));
		if ($isComment || empty($lines[$lineIndex])) {
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