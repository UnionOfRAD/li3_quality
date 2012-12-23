<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;

class OperatorSpacing extends \li3_quality\test\Rule {

	/**
	 * The current spacing rules in place
	 *
	 * @var array
	 */
	public $inspector = array(
		'oneSpace' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 3,
			),
			'regex' => '/^ {:content} $/',
			'message' => 'Operator {:content} must only have one leading and trailing space.',
			'tokens' => array(
				T_AND_EQUAL,
				T_AS,
				T_BOOLEAN_AND,
				T_BOOLEAN_OR,
				T_CLONE,
				T_CONCAT_EQUAL,
				T_DIV_EQUAL,
				T_IS_EQUAL,
				T_IS_IDENTICAL,
				T_IS_NOT_EQUAL,
				T_IS_NOT_IDENTICAL,
				T_IS_SMALLER_OR_EQUAL,
				T_LOGICAL_AND,
				T_LOGICAL_OR,
				T_LOGICAL_XOR,
				T_MINUS_EQUAL,
				T_MOD_EQUAL,
				T_MUL_EQUAL,
				T_OR_EQUAL,
				T_PLUS_EQUAL,
				T_SL,
				T_SL_EQUAL,
				T_SR,
				T_SR_EQUAL,
				T_XOR_EQUAL,
				T_INSTANCEOF,
			),
			'content' => array(
				'+',
				'*',
				'/',
			),
		),
		'equals' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 4,
			),
			'regex' => '/ (\\=|\\=\\&) /',
			'message' => 'Operator {:content} must only have one leading and trailing space.',
			'tokens' => array(),
			'content' => array(
				'=',
			),
		),
		'optionalEnding' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 3,
			),
			'regex' => '/^ {:content}( )?$/',
			'tokens' => array(),
			'message' => 'Operator {:content} must have 1 leading and an optional trailing space.',
			'content' => array(
				'.',
			),
		),
		'negativeNumber' => array(
			'relativeTokens' => array(
				'before' => 2,
				'length' => 5,
			),
			'regex' => '/(( {:content} )|([^\d]{:content}\d+))/',
			'tokens' => array(),
			'message' => 'Operator {:content} must have 1 leading and an optional trailing space.',
			'content' => array(
				'-',
			),
		),
		'noSpace' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 3,
			),
			'regex' => '/[^ ]{:content}[^ ]/',
			'message' => 'Operator {:content} must not be be surrounded by 1 space.',
			'tokens' => array(
				T_DOUBLE_COLON,
				T_PAAMAYIM_NEKUDOTAYIM,
				T_OBJECT_OPERATOR,
			),
			'content' => array(),
		),
		'oneOrMoreSpace' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 3,
			),
			'regex' => '/^[ ]+{:content} $/',
			'message' => 'Operator {:content} must have 1 trailing space and 1+ leading spaces.',
			'tokens' => array(
				T_DOUBLE_ARROW,
			),
			'content' => array(),
		),
		'ternarySpacing' => array(
			'relativeTokens' => array(
				'before' => 3,
				'length' => 7,
			),
			'fullLineRegex' => '/^\s+(case|default)(.*):$/',
			'regex' => '/(([^ ] (\?:|\?|:) [^ ])|(:$))/',
			'message' => 'Operator {:content} must be surrounded by spaces.',
			'tokens' => array(),
			'content' => array(
				':',
				'?',
			),
		),
	);

	/**
	 * With lots of various rules we created 4 various rulesets for operators. If one
	 * of the tokens or content is found we use the given regex on the joined array.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$tokens = $testable->tokens();
		foreach ($tokens as $id => $token) {
			$pattern = false;
			foreach ($this->inspector as $inspector) {
				$hasTokens = in_array($token['id'], $inspector['tokens']);
				$hasContent = in_array($token['content'], $inspector['content']);
				$badToken = $token['id'] === T_ENCAPSED_AND_WHITESPACE;
				if (($hasTokens || $hasContent) && !$badToken) {
					$pattern = String::insert($inspector['regex'], array(
						'content' => preg_quote($token['content'], "/"),
					));
					$firstId = $id - $inspector['relativeTokens']['before'];
					$firstId = ($firstId < 0) ? 0 : $firstId;
					$length = $inspector['relativeTokens']['length'];
					$inspectTokens = array_slice($tokens, $firstId, $length);
					$html = null;
					foreach ($inspectTokens as $htmlToken) {
						if ($htmlToken['line'] === $token['line']) {
							$html .= $htmlToken['content'];
						}
					}
					$html = preg_split('/\r\n|\r|\n/', $html);
					if (preg_match($pattern, $html[0]) === 0) {
						$this->addViolation(array(
							'message' => String::insert($inspector['message'], $token),
							'line' => $token['line'],
						));
					}
				}
			}
		}
	}

}

?>