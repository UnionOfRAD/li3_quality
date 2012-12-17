<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;

class HasCorrectDocblockStyle extends \li3_quality\test\Rule {

	/**
	 * The regexes to use on detecting docblocks
	 *
	 * @var array
	 */
	public $patterns = array(
		'NO_LEVEL'      => '/{:begin}\/\*\*({:wline} \*( (.*))?)+{:wline} \*\/$/',
		'FIRST_LEVEL'   => '/{:begin}\t\/\*\*({:wline}\t \*( (.*))?)+{:wline}\t \*\/$/',
		'HAS_TAGS'      => '/ \* @/',
		'TAG_FORMAT'    => array(
			'/',
			'{:begin}\t?\/\*\*',
			'(({:wlinet} \*( [^@].*)?)+)',
			'{:wlinet} \*',
			'(({:wlinet} \* @(.*)))',
			'(({:wlinet} \* (@|[ ]{5})(.*))+)?',
			'{:wlinet} \*\/',
			'/',
		),
	);

	/**
	 * Patterns to replace inside the regex to make them shorter and easier to read
	 *
	 * @var array
	 */
	public $regexInject = array(
		'begin' => '(^|\r\n|\r|\n)',
		'wline' => '(\r\n|\r|\n)',
		'wlinet' => '(\r\n|\r|\n)\t?',
	);

	/**
	 * Will iterate tokens looking for comments and if found will determine the regex
	 * to test the comment against.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$tokens = $testable->tokens();
		foreach ($testable->findAll(array(T_DOC_COMMENT)) as $tokenId) {
			$token = $tokens[$tokenId];
			$level = $token['level'];
			$parent = $token['parent'];
			$inClass = $testable->tokenIn(array(T_CLASS), $tokenId);
			$inFunction = $testable->tokenIn(array(T_FUNCTION), $tokenId);
			$inVariable = $testable->tokenIn(array(T_VARIABLE), $tokenId);
			$content = null;

			if ($inClass && ($inFunction || $inVariable)) {
				$match = 'FIRST_LEVEL';
				if (isset($tokens[$tokenId - 1]) && $tokens[$tokenId - 1]['id'] === T_WHITESPACE) {
					$content .= $tokens[$tokenId - 1]['content'];
				}
			} elseif ($level === 0 && ($inClass XOR $inFunction XOR $token['line'] === 1)) {
				$match = 'NO_LEVEL';
			} else {
				$this->addViolation(array(
					'message' => 'Docblocks should only be at the beginning of the page or ' .
						'before a class/function.',
					'line' => $token['line'],
				));
				continue;
			}

			$content .= $token['content'];

			$correctFormat = preg_match($this->compilePattern($match), $content) === 1;
			$hasTags = preg_match($this->compilePattern('HAS_TAGS'), $content) === 1;
			$correctTagFormat = preg_match($this->compilePattern('TAG_FORMAT'), $content) === 1;
			if (!$correctFormat) {
				$this->addViolation(array(
					'message' => 'Docblocks are in the incorrect format.',
					'line' => $token['line'],
				));
			} elseif ($hasTags && !$correctTagFormat) {
				$this->addViolation(array(
					'message' => 'Tags should be last and have a blank docblock line.',
					'line' => $token['line'],
				));
			}
		}
	}

	/**
	 * A helper method to help compile patterns
	 *
	 * @param  string $key
	 * @return string
	 */
	public function compilePattern($key) {
		$items = $this->patterns[$key];
		if (is_array($items)) {
			$items = implode(null, $items);
		}
		return String::insert($items, $this->regexInject);
	}

}

?>