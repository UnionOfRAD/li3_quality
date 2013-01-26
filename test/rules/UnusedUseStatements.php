<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

class UnusedUseStatements extends \li3_quality\test\Rule {

	/**
	 * Will iterate over each line checking if tabs are only first
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$useStatements = array();
		$regexArray = array();
		$compiledRegex = '/$^/i';
		$tokens = $testable->tokens();
		$lines = $testable->lines();
		$typeCache = $testable->typeCache();
		$matches = array();
		if (!isset($typeCache[T_USE])) {
			return;
		}
		foreach ($typeCache[T_USE] as $tokenId) {
			$token = $tokens[$tokenId];
			$line = $lines[$token['line'] - 1];
			if (preg_match('/^use (?:([^ ]+ as )|(.*\\\))?(.*);$/i', $line, $matches) === 1) {
				$useStatements[strtolower($matches[3])] = $token['line'];
				$regexArray[] = "{$matches[3]}";
			}
		}
		$compiledRegex = $this->_compileRegex($regexArray);
		foreach ($lines as $line) {
			$foundUse = preg_match('/^use/', $line) === 1;
			$foundStatement = preg_match_all($compiledRegex, $line, $matches) !== false;
			if (!$foundUse && $foundStatement) {
				foreach ($matches[0] as $match) {
					unset($useStatements[strtolower($match)]);
				}
				$compiledRegex = $this->_compileRegex($regexArray);
			}
		}
		foreach ($useStatements as $useStatement => $line) {
			$this->addViolation(array(
				'message' => 'Class ' . $useStatement . ' was never called',
				'line' => $line,
			));
		}
	}

	/**
	 * Will compile a new regex to search each line for.
	 *
	 * The regex is compiled after every potential change to the regex array.
	 *
	 * @param  array  $regexArray
	 * @return string
	 */
	protected function _compileRegex(array $regexArray = array()) {
		usort($regexArray, function($a, $b) { return strlen($b) - strlen($a); });
		return '/' . implode('|', $regexArray) . '/i';
	}

}

?>