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
	public function apply($testable, array $config = array()) {
		$followerCount = 0;
		$lines = $testable->lines();
		$message = 'Incorrect tab indention {:actual} should be {:predicted}.';
		foreach ($lines as $lineIndex => $line) {
			if (!$this->_shouldIgnoreLine($lineIndex, $testable)) {
				$actual = $this->_beginningTabCount($line);
				$predicted = $this->_beginningTabCountPredicted($lineIndex, $testable);
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
	protected function _beginningTabCountPredicted($lineIndex, $testable) {
		$tokens = $testable->tokens();
		$lines = $testable->lines();
		$lineCache = $testable->lineCache();

		$line = trim($lines[$lineIndex]);
		$lineLen = strlen($line);
		$prevLine = $lineIndex > 0 ? trim($lines[$lineIndex - 1]) : false;
		$prevLen = strlen($prevLine);

		$currentTokens = $lineCache[$testable->findTokensByLine($lineIndex + 1)];
		$endingTokens = array(T_ENDFOR, T_ENDFOREACH, T_ENDIF, T_ENDSWITCH, T_ENDWHILE);
		$switch = false;

		if ($lineLen > 0 && ($line[0] === ")" || $line[0] === "}")) {
			$ending = $testable->findNextContent(array('}'), $currentTokens);
			$child = isset($tokens[$ending]) ? $tokens[$ending] : false;
			$parent = isset($tokens[$child['parent']]) ? $tokens[$child['parent']] : false;
			if ($parent && $parent['id'] === T_SWITCH && $line[0] === "}") {
				$this->_currentCount -= 2;
			} else {
				$this->_currentCount -= 1;
			}
		}
		if ($lineLen > 0 && $line[$lineLen - 1] === ":") {
			$elif = strpos($line, "elseif") !== false || strpos($line, "else if") !== false;
			$else = $line[$lineLen - 5] . $line[$lineLen - 4];
			$else .= $line[$lineLen - 3] . $line[$lineLen - 2];
			if ($elif || $else === "else") {
				$this->_currentCount -= 1;
			}
		}

		$currentCount = $this->_currentCount;

		$termOp = $lineLen > 1 && $line[0] === "-" && $line[1] === ">";
		$boolAnd = $boolOr = $logAnd = $logOr = false;
		$decimal = $prevLen > 0 && $prevLine[$prevLen - 1] === ".";
		if ($prevLen > 2) {
			$last = $prevLine[$prevLen - 3] . $prevLine[$prevLen - 2] . $prevLine[$prevLen - 1];
			$logAnd = strtolower($last) === 'and';
		}
		if ($prevLen > 1) {
			$last = $prevLine[$prevLen - 2] . $prevLine[$prevLen - 1];
			$boolAnd = $last === '&&';
			$boolOr = $last === '||';
			$logOr = strtolower($last) === 'or';
		}

		if ($termOp || $boolAnd || $boolOr || $logAnd || $logOr || $decimal) {
			$currentCount += 1;
		}

		$switch = false;
		$find = false;
		$find = $find || substr($line, -6) === "break;";
		$find = $find || substr($line, -8) === "default:";
		$find = $find || ($line[$lineLen - 1] === ":" && strpos($line, "case") !== false);
		if ($find) {
			$childContent = array('case', 'default', 'break');
			$child = $tokens[$testable->findNextContent($childContent, $currentTokens)];
			if (isset($tokens[$child['parent']]) && $tokens[$child['parent']]['id'] === T_SWITCH) {
				$currentCount -= 1;
				$switch = true;
			}
		}

		$find = !$switch && in_array($line[$lineLen - 1], array("{", ":", "("));
		$found = false;
		foreach ($currentTokens as $tokenKey) {
			if (in_array($tokens[$tokenKey]['id'], $endingTokens, true)) {
				$currentCount -= 1;
				$this->_currentCount -= 1;
			}
			if ($find && $tokens[$tokenKey]['id'] === T_SWITCH) {
				$this->_currentCount += 2;
				$found = true;
			}
		}
		if ($find && !$found) {
			$this->_currentCount += 1;
		}
		return $currentCount;
	}

	/**
	 * Will determine how many tabs are at the beginning of a line.
	 *
	 * @param  string $line The current line is on
	 * @return bool
	 */
	protected function _beginningTabCount($line) {
		$count = 0;
		$end = strlen($line);
		while (($count < $end) && ($line[$count] === "\t")) {
			$count++;
		}
		return $count;
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
	protected function _shouldIgnoreLine($lineIndex, $testable) {
		$lines = $testable->lines();
		$line = $lines[$lineIndex];
		$plain = trim($line);

		if (empty($plain)) {
			return true;
		}

		$length = strlen($plain);
		if ($length > 1 && ($plain[0] . $plain[1] === "//")) {
			return true;
		} else if ($length > 2 && ($plain[0] . $plain[1] . $plain[2] === "/**")) {
			return true;
		} else if ($plain[0] === "#" || $plain[0] === "*") {
			return true;
		}

		$stringTokens = array(T_ENCAPSED_AND_WHITESPACE, T_END_HEREDOC);
		$tokens = $testable->tokens();
		$start = $testable->findTokenByLine($lineIndex + 1);
		return in_array($tokens[$start]['id'], $stringTokens, true);
	}

}

?>