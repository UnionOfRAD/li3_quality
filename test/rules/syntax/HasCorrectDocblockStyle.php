<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules\syntax;

use lithium\util\String;

class HasCorrectDocblockStyle extends \li3_quality\test\Rule {

	/**
	 * The regexes to use on detecting docblocks
	 *
	 * @var array
	 */
	public $patterns = array(
		'PAGE'          => '/\/\*\*({:wline} \*( (.*))?)+{:wline} \*\/$/',
		'CLASS'         => '/\/\*\*({:wline} \*( (.*))?)+{:wline} \*\/$/',
		'VARIABLE'      => '/{:begin}\t?\/\*\*({:wline}\t? \*( (.*))?)+{:wline}\t? \*\/$/',
		'METHOD'        => '/{:begin}\t?\/\*\*({:wline}\t? \*( (.*))?)+{:wline}\t? \*\/$/',
		'HAS_TAGS'      => '/ \* @/',
		'TAG_FORMAT'    => array(
			'/',
			'{:begin}\t?\/\*\*',
			'((({:wlinet} \*( [^@].*)?)+)',
			'{:wlinet} \*)?',
			'(({:wlinet} \* @(.*)))',
			'(({:wlinet} \* (@|[ ]{5})(.*))+)?',
			'{:wlinet} \*\/',
			'/'
		)
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
	public function apply($testable, array $config = array()) {
		$tokens = $testable->tokens();
		$lines = $testable->lines();
		$lineCache = $testable->lineCache();
		$inspectable = array(T_CLASS, T_VARIABLE, T_FUNCTION, T_CONST, T_DOUBLE_COLON);
		foreach ($testable->findAll(array(T_DOC_COMMENT)) as $tokenId) {
			$token = $tokens[$tokenId];
			$nextLine = $token['line'] + count(preg_split('/\r\n|\r|\n/', $token['content']));
			$parentId = false;
			if (isset($lineCache[$nextLine])) {
				$parentId = $testable->findNext($inspectable, $lineCache[$nextLine]);
			}
			if ($parentId === false && $token['line'] !== 2) {
				$this->addViolation(array(
					'message' => 'Docblocks should only be at the beginning of the page or ' .
					             'before a class/function or static call.',
					'line' => $token['line'],
				));
				continue;
			}

			$parent = $tokens[$parentId];
			$content = null;

			if ($token['line'] === 2) {
				$match = 'PAGE';
			} else {
				switch ($parent['id']) {
					case T_CLASS:
						$match = 'CLASS';
					break;
					case T_DOUBLE_COLON:
					case T_FUNCTION:
						$match = 'METHOD';
					break;
					case T_VARIABLE:
					case T_CONST:
						$match = 'VARIABLE';
					break;
				}
			}
			if (in_array($parent['id'], array(T_FUNCTION, T_VARIABLE), true)) {
				$content .= $tokens[$tokenId - 1]['content'];
			}
			$content .= $token['content'];

			$pattern = $this->compilePattern($match);
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
					'hasTags' => (int) $hasTags,
					'correctTagFormat' => (int) $correctTagFormat,
					'tagFormat' => $this->compilePattern('TAG_FORMAT'),
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