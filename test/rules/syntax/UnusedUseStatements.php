<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules\syntax;

class UnusedUseStatements extends \li3_quality\test\Rule {

	/**
	 * Use statements to ignore if declared but not used.
	 */
	protected $_ignored = array(
		'ArrayAccess',
		'Closure',
		'Iterator',
		'IteratorAggregate',
		'Serializable',
		'Traversable'
	);

	/**
	 * Iterates over T_USE tokens, gets the aliased name into an array and
	 * validates it was used within the script.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
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
				$count = 0;
				if (in_array($matches[3], $this->_ignored)) {
					continue;
				}
				foreach ($typeCache[T_STRING] as $stringId) {
					if (strcasecmp($tokens[$stringId]['content'], $matches[3]) === 0) {
						$count++;
					}
					if ($count === 2) {
						break;
					}
				}
				if ($count < 2) {
					$this->addViolation(array(
						'message' => 'Class ' . $matches[3] . ' was never called',
						'line' => $token['line'],
					));
				}
			}
		}
	}

}

?>