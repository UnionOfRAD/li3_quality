<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;

class HasCorrectCommentStyle extends \li3_quality\test\Rule {

	/**
	 * The PHP 5+ comment tokens
	 * @var array
	 */
	public $inspectableTokens = array(
		T_COMMENT,
		T_DOC_COMMENT,
	);

	/**
	 * The regexes to use on detecting docblocks
	 * @var array
	 */
	public $patterns = array(
		'PAGE_LEVEL'    => '/(^|{:line})\/\*\*(({:line}) \*( (.*))?)+({:line}) \*\/$/',
		'CLASS_LEVEL'   => '/(^|{:line})\t\/\*\*(({:line})\t \*( (.*))?)+({:line})\t \*\/$/',
		'TEST_LEVEL'    => '/\s?\/\/( (.*))?$/',
		'TEST_FUNCTION' => '/^test/',
	);

	/**
	 * Patterns to replace inside the regex to make them shorter and easier to read
	 * @var array
	 */
	public $regexInject = array(
		'line' => '\r\n|\r|\n',
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
		foreach ($tokens as $tokenId => $token) {
			if (in_array($token['id'], $this->inspectableTokens)) {
				$inClass = $this->tokenIn($tokens, array(T_CLASS), $tokenId);
				$inFunction = $this->tokenIn($tokens, array(T_FUNCTION), $tokenId);
				$content = null;
				if ($inClass && $inFunction) {
					$function = $this->findPrev($tokens, array(T_FUNCTION), $tokenId);
					$functionNameId = $this->findNext($tokens, array(T_STRING), $function);
					$pattern = String::insert(
						$this->patterns['TEST_FUNCTION'],
						$this->regexInject
					);
					if (preg_match($pattern, $tokens[$functionNameId]['content']) === 0) {
						$this->addViolation(array(
							'message' => 'Comments should not appear in methods.',
							'line' => $token['line'],
						));
					}
					$match = 'TEST_LEVEL';
				} elseif ($inClass XOR $inFunction) {
					$match = 'CLASS_LEVEL';
				} elseif (!$inClass && !$inFunction) {
					$match = 'PAGE_LEVEL';
				}
				if (isset($tokens[$tokenId - 1]) && $tokens[$tokenId - 1]['id'] === T_WHITESPACE) {
					$content .= $tokens[$tokenId - 1]['content'];
				}
				$content .= $token['content'];
				$pattern = String::insert(
					$this->patterns[$match],
					$this->regexInject
				);
				if (preg_match($pattern, $content) === 0) {
					$this->addViolation(array(
						'inClass' => $inClass,
						'inFunction' => $inFunction,
						'match' => $match,
						'content' => $content,
						'message' => 'Docblocks are in the incorrect format.',
						'line' => $token['line'],
					));
				}
			}
		}
	}

}

?>