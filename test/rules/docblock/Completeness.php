<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules\docblock;

use lithium\analysis\Docblock;

/**
 * Will determine the completeness of the docblocks.
 * Throws violations when missing:
 *  * Required docblocks
 *  * Required sections
 *  * Required tags
 */
class Completeness extends \li3_quality\test\Rule {

	/**
	 * Will determine if the given file is ignoreable.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return bool
	 */
	protected function _ignore($testable, array $config = array()) {
		if (!isset($config['excludeFiles'])) {
			return false;
		}
		return preg_match($config['excludeFiles'], $testable->config('path')) === 1;
	}

	/**
	 * Will return a list of inspectable docblock by type. Will also throw
	 * violations for missing docblock tags.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		if ($this->_ignore($testable, $config)) {
			return;
		}
		$docblocks = $this->inspectableDocBlocks($testable, $config);
		$tokens = $testable->tokens();
		foreach ($docblocks as $type => $items) {
			$required = $config[$type];
			foreach ($items as $item) {
				$token = $tokens[$item];
				$docblock = Docblock::comment($token['content']);
				foreach ($required['sections'] as $section) {
					if (!isset($docblock[$section])) {
						$this->addViolation(array(
							'message' => 'Docblock has no required section ' . $section . '.',
							'line' => $token['line'],
						));
					}
				}
				foreach ($required['tags'] as $tag) {
					if (!isset($docblock['tags'][$tag])) {
						$this->addViolation(array(
							'message' => 'Docblock has no required tag ' . $tag . '.',
							'line' => $token['line'],
						));
					}
				}
			}
		}
	}

	/**
	 * Will return a list of inspectable docblock by type. Will also throw
	 * violations for missing docblock tags.
	 *
	 * @param  Testable $testable The testable object
	 * @param  array    $config
	 * @return array
	 */
	public function inspectableDocBlocks($testable, array $config = array()) {
		$tokens = $testable->tokens();
		$types = $testable->typeCache();
		$lineCache = $testable->lineCache();

		$class = $types[T_CLASS];
		$children = $tokens[$class[0]]['children'];
		$variable = $testable->findAll(array(T_VARIABLE), $children);
		$method = $testable->findAll(array(T_FUNCTION), $children);

		$docblocks = array(
			'page' => $testable->findAll(array(T_DOC_COMMENT), $lineCache[2]),
		);

		foreach (compact('class', 'variable', 'method') as $type => $items) {
			$docblocks[$type] = array();
			foreach ($items as $item) {
				$tokenLine = $tokens[$item]['line'];
				$prevToken = $testable->findTokenByLine($tokenLine - 2);
				$prevLine = $tokens[$prevToken]['line'];
				$docblock = $testable->findNext(array(T_DOC_COMMENT), $lineCache[$prevLine]);
				if ($docblock !== false) {
					$docblocks[$type][] = $docblock;
				} elseif ($config[$type]['required']) {
					$this->addViolation(array(
						'message' => "Token {$tokens[$item]['name']} does not have docblocks",
						'line' => $tokenLine,
					));
				}
			}
		}
		return $docblocks;
	}

}

?>