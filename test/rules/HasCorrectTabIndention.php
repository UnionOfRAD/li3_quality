<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;
use lithium\analysis\Parser;

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
	 * Where the current tab count is held. This is a very relative process and a close count
	 * shoudl be kept.
	 *
	 * @var integer
	 */
	protected $_currentCount = 0;

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
		$message = 'Incorrect tab indention {:actual} should be {:predicted}.';
		foreach ($lines as $lineIndex => $line) {
			if (!$this->_shouldIgnoreLine($lineIndex, $testable)) {
				$predicted = $this->_beginningTabCountPredicted($lineIndex, $testable);
				$actual = $this->_beginningTabCount($lineIndex, $testable);
				if ($predicted !== $actual) {
					$this->addViolation(array(
						'message' => String::insert($message, array(
							'predicted' => $predicted,
							'actual' => $actual,
						)),
						'line' => $lineIndex + 1,
					));
				}
			}
		}
	}

	/**
	 * Will determine how many tabs a current line should have.
	 * This makes use of an instance variable to track relative movements in
	 * tabs, calling this method out of order will have unexpected results.
	 *
	 * @param  int    $lineIndex The index the current line is on
	 * @param  array  $testable  The testable object
	 * @return int
	 */
	protected function _beginningTabCountPredicted($lineIndex, &$testable) {
		$tokens = $testable->tokens();
		$lineCache = $testable->lineCache();
		$lines = $testable->lines();
		$line = $lines[$lineIndex];
		$endingTokens = array(T_ENDFOR, T_ENDFOREACH, T_ENDIF, T_ENDSWITCH, T_ENDWHILE);
		$currentTokens = $lineCache[$testable->findTokensByLine($lineIndex + 1)];
		$currentCount = $this->_currentCount;
		$switch = false;

		foreach ($currentTokens as $tokenKey) {
			if (in_array($tokens[$tokenKey]['id'], $endingTokens, true)) {
				$currentCount = $this->_currentCount -= 1;
				break;
			}
		}
		if (isset($lines[$lineIndex - 1]) && preg_match('/\.\s*$/', $lines[$lineIndex - 1]) === 1) {
			$currentCount = $this->_currentCount + 1;
		}
		if (preg_match('/^\s*(\)|\})/', $line) === 1) {
			$hasEndingBracket = $testable->findNextContent(array('}'), $currentTokens);
			$child = $tokens[$hasEndingBracket];
			if ($hasEndingBracket !== false && $tokens[$child['parent']]['id'] === T_SWITCH) {
				$currentCount = $this->_currentCount -= 2;
			} else {
				$currentCount = $this->_currentCount -= 1;
			}
		}
		if (preg_match('/(case(.*):|default:|break)\s*$/', $line) === 1) {
			$childContent = array('case', 'default', 'break');
			$child = $tokens[$testable->findNextContent($childContent, 0, $currentTokens)];
			if (isset($tokens[$child['parent']]) && $tokens[$child['parent']]['id'] === T_SWITCH) {
				$currentCount = $this->_currentCount - 1;
				$switch = true;
			}
		}
		if (preg_match('/(else( )?if(.*):|else:)\s*$/', $line) === 1) {
			$currentCount = $this->_currentCount -= 1;
		}

		if (!$switch && preg_match('/(\{|:|\()\s*$/', $line) === 1) {
			$found = false;
			foreach ($currentTokens as $token) {
				if ($tokens[$token]['id'] === T_SWITCH) {
					$this->_currentCount += 2;
					$found = true;
					break;
				}
			}
			if (!$found) {
				$this->_currentCount += 1;
			}
		}
		return $currentCount;
	}

	/**
	 * Will determine how many tabs are at the beginning of a line.
	 *
	 * @param  int    $lineIndex The index the current line is on
	 * @param  array  $testable  The testable object
	 * @return bool
	 */
	protected function _beginningTabCount($lineIndex, &$testable) {
		$lines = $testable->lines();
		$line = $lines[$lineIndex];
		preg_match_all('/^(\t+)[^\t]/', $line, $matches);
		return isset($matches[1][0]) ? strlen($matches[1][0]) : 0;
	}

	/**
	 * Will determine if the line is ignoreable.
	 * Currently the line is ignoreable if:
	 *  * Empty Line
	 *  * In docblocks
	 *  * In heredocs
	 *
	 * @param  int    $lineIndex The index the current line is on
	 * @param  array  $testable  The testable object
	 * @return bool
	 */
	protected function _shouldIgnoreLine($lineIndex, &$testable) {
		$lines = $testable->lines();
		$tokens = $testable->tokens();
		$lineCache = $testable->lineCache();
		$line = $lineIndex + 1;
		$stringTokens = array(T_ENCAPSED_AND_WHITESPACE, T_END_HEREDOC);

		$tokensStart = $testable->findTokensByLine($line);
		$currentTokens = $lineCache[$tokensStart];
		$start = $currentTokens[($tokensStart === $line) ? 0 : count($currentTokens) - 1];

		$start = $testable->findTokenByLine($lineIndex + 1, $tokens);
		$isComment = preg_match('/^\s*(\/\/|\*|\/\*\*|#)/', $lines[$lineIndex]) === 1;
		$isEmpty = empty($lines[$lineIndex]);
		$isString = in_array($tokens[$start]['id'], $stringTokens, true);
		return $isComment || $isEmpty || $isString;
	}

}

?>