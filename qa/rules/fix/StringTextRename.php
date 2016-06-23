<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2015, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa\rules\fix;

class StringTextRename extends \li3_quality\qa\Rule {

	public function enabled($testable, array $config = array()) {
		return $testable->isPHP();
	}

	public function apply($testable, array $config = array()) {
		$contents = $testable->source();

		$contents = str_replace('use lithium\util\String;', 'use lithium\util\Text;', $contents);

		foreach (array('uuid', 'insert', 'clean', 'extract') as $function) {
			$contents = str_replace('String::' . $function, 'Text::' . $function, $contents);
		}
		foreach (array('hash', 'compare') as $function) {
			$contents = str_replace('String::' . $function, 'Hash::' . $function, $contents, $count);
			if ($count) {
				$testable->source($contents);
				$this->_addDependency($testable, 'lithium\security\Hash');
			}
		}
		foreach (array('random', 'ENCODE_BASE_64') as $function) {
			$contents = str_replace('String::' . $function, 'Random::' . $function, $contents, $count);
			if ($count) {
				$testable->source($contents);
				$this->_addDependency($testable, 'lithium\security\Random');
			}
		}
	}

	/**
	 * Place use statements below other use statements or if not present 1 line after the
	 * namespace declaration.
	 *
	 * @param object $testable
	 * @param string $class
	 * @return void
	 */
	protected function _addDependency($testable, $class) {
		if ($testable->findNextContent(array("use {$class};"))) {
			return;
		}
		$tokens = $testable->tokens();
		$lines = $testable->lines();

		if ($token = $testable->findNext(array(T_USE))) {
			array_splice($lines, $tokens[$token]['line'] - 1, 0, "use {$class};");
		} else {
			if (!$token = $testable->findNext(array(T_NAMESPACE))) {
				$this->addWarning(array('message' => 'Not able to add dependency.'));
				return;
			}
			array_splice($lines, $tokens[$token]['line'], 0, "use {$class};");
			array_splice($lines, $tokens[$token]['line'], 0, "");
		}
		$testable->source(implode("\n", $lines));
	}
}

?>