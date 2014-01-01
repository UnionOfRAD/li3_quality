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
	 * Will iterate the lines looking for $patterns while keeping track of how many tabs
	 * the current line should have.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$followerCount = 0;
		$lines = $testable->lines();
		$tabMessage = 'Incorrect tab indention {:actual} should be {:predicted}.';
		$spaceMessage = 'Incorrect space indention {:actual} should be >= {:predicted}.';
		foreach ($lines as $lineIndex => $line) {
			if (!$this->_shouldIgnoreLine($lineIndex, $testable)) {
				$actual = $this->_getIndent($line);
				$predicted = $this->_getPredictedIndent($lineIndex, $testable);
				if ($predicted['tab'] !== null && $actual['tab'] !== $predicted['tab']) {
					$this->addViolation(array(
						'message' => String::insert($tabMessage, array(
							'predicted' => $predicted['tab'],
							'actual' => $actual['tab'],
						)),
						'line' => $lineIndex + 1,
					));
				}
				if ($predicted['minSpace'] !== null && $actual['space'] < $predicted['minSpace']) {
					$this->addViolation(array(
						'message' => String::insert($spaceMessage, array(
							'predicted' => $predicted['minSpace'],
							'actual' => $actual['space'],
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
	protected function _getPredictedIndent($lineIndex, $testable) {
		$result = array('minSpace' => null,'tab' => null);
		$tokens = $testable->tokens();
		$lines = $testable->lines();
		$lineCache = $testable->lineCache();

		$line = trim($lines[$lineIndex]);
		$lineLen = strlen($line);
		$prevLine = $lineIndex > 0 ? trim($lines[$lineIndex - 1]) : false;
		$prevLen = strlen($prevLine);

		$currentTokens = $lineCache[$testable->findTokensByLine($lineIndex + 1)];
		$firstToken = $tokens[reset($currentTokens)];

		if ($firstToken['id'] === T_WHITESPACE) {
			$firstToken = $tokens[next($currentTokens)];
		}

		if (!isset($firstToken['level'])) {
			return $result;
		}

		$parentId = $firstToken['parent'];
		$parent = isset($tokens[$parentId]) ? $tokens[$parentId] : null;
		$expectedTab = $firstToken['nestLevel'];

		if ($expectedTab === null || ($firstToken['line'] !== $lineIndex + 1)) {
			return $result;
		}

		$breaked = false;
		foreach (array('&&', '||', 'and', 'or', 'xor', 'AND', 'OR', 'XOR', '.') as $op) {
			$op = preg_quote($op);
			if (preg_match("/\s+$op$/", $prevLine)) {
				$breaked = true;
			}
		}

		$minExpectedSpace = 0;

		$inArray = ($parent !== null && (
			$parent['id'] === T_ARRAY_OPEN || $parent['id'] === T_SHORT_ARRAY_OPEN
		));
		$inBrace = ($parent !== null && (
			$parent['content'] === '(' || $parent['content'] === '['
		));
		if ($breaked) {
			if ($inArray) {
				$minExpectedSpace = $this->_getSpaceAlignmentInArray($lineIndex, $testable);
			}
			if (!$inBrace) {
				$expectedTab += 1;
			}
		}

		if (preg_match('/^->/', $line) && !$inBrace) {
			$expectedTab += 1;
		}

		if ($inArray) {
			$grandParent = $parent['parent'] > -1 ? $tokens[$parent['parent']] : null;
			if ($grandParent !== null) {
				$grandParentLine = trim($lines[$grandParent['line'] - 1]);

				if (preg_match('/^->/', $grandParentLine)) {
					$expectedTab += 1;
				}
			}
		}

		return array(
			'minSpace' => $minExpectedSpace,
			'tab' => $expectedTab
		);
	}

	/**
	 * Return the minimal space required for a multilined expression in an array definition.
	 *
	 * @param  int    $lineIndex The index the current line is on
	 * @param  array  $testable  The testable object
	 * @return int
	 */
	protected function _getSpaceAlignmentInArray($lineIndex, $testable) {
		if (!$lineIndex) {
			return;
		}

		$tokens = $testable->tokens();
		$lines = $testable->lines();
		$lineCache = $testable->lineCache();

		$prevLine = $lines[$lineIndex - 1];
		$previousTokens = $lineCache[$testable->findTokensByLine($lineIndex)];
		$alignTo = 0;
		$len = 0;
		foreach ($previousTokens as $tokenId) {
			$len += strlen($tokens[$tokenId]['content']);
			if ($tokens[$tokenId]['content'] === '=>') {
				$max = strlen($prevLine);
				while ($len + 1 < $max) {
					if ($prevLine[$len + 1] !== ' ') {
						break;
					}
					$len++;
				}
				$alignTo = $len;
			}
		}
		return $alignTo === null ? $alignTo = 0 : $alignTo;
	}

	/**
	 * Will determine how many tabs are at the beginning of a line.
	 *
	 * @param  string $line The current line is on
	 * @return bool
	 */
	protected function _getIndent($line) {
		$count = $space = $tab = 0;
		$end = strlen($line);
		while (($count < $end) && ($line[$count] === "\t")) {
			$tab++;
			$count++;
		}
		while (($count < $end) && ($line[$count] === ' ')) {
			$space++;
			$count++;
		}
		return array(
			'space' => $space,
			'tab' => $tab
		);
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
		} elseif ($length > 2 && ($plain[0] . $plain[1] . $plain[2] === "/**")) {
			return true;
		} elseif ($plain[0] === "#" || $plain[0] === "*") {
			return true;
		}

		$stringTokens = array(T_ENCAPSED_AND_WHITESPACE, T_END_HEREDOC);
		$tokens = $testable->tokens();
		$start = $testable->findTokenByLine($lineIndex + 1);
		return in_array($tokens[$start]['id'], $stringTokens, true);
	}

}

?>