<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa\rules\syntax;

use lithium\util\Text;

class OperatorSpacing extends \li3_quality\qa\Rule {

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
				T_CLONE,
				T_CONCAT_EQUAL,
				T_DIV_EQUAL,
				T_IS_EQUAL,
				T_IS_IDENTICAL,
				T_IS_NOT_EQUAL,
				T_IS_NOT_IDENTICAL,
				T_IS_SMALLER_OR_EQUAL,
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
				'*',
				'/',
			),
		),
		'oneSpaceEOL' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 3,
			),
			'regex' => '/^ {:content}([ ]$|\n)/',
			'message' => 'Operator {:content} must only have one leading and trailing space.',
			'tokens' => array(
				T_BOOLEAN_AND,
				T_BOOLEAN_OR,
				T_LOGICAL_AND,
				T_LOGICAL_OR,
				T_LOGICAL_XOR,
			),
		),
		'noSpaceAfter' => array(
			'relativeTokens' => array(
				'before' => 0,
				'length' => 2,
			),
			'regex' => '/{:content}[^ ]/',
			'message' => 'Operator {:content} must not be followed by a space.',
			'content' => array(
				'[',
				'('
			),
		),
		'noSpaceBefore' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 2,
			),
			'regex' => '/[^ ]{:content}/',
			'message' => 'Operator {:content} must not be preceded by a space.',
			'content' => array(
				')',
				']'
			),
		),
		'equals' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 4,
			),
			'regex' => '/ (\\=|\\=\\&) /',
			'message' => 'Operator {:content} must only have one leading and trailing space.',
			'content' => array(
				'=',
			),
		),
		'optionalEnding' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 3,
			),
			'regex' => '/^ {:content}( )?($|\n)/',
			'message' => 'Operator {:content} must have 1 leading and an optional trailing space.',
			'content' => array(
				'.',
			),
		),
		'plusAndMinus' => array(
			'relativeTokens' => array(
				'before' => 2,
				'length' => 5,
			),
			'regex' => '/(( {:content} )|([^\d]{:content}(.|\d+|\$)))/',
			'message' => 'Operator {:content} must have 1 leading and an optional trailing space.',
			'content' => array(
				'+',
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
			),
		),
		'objectOperator' => array(
			'relativeTokens' => array(
				'before' => 1,
				'length' => 3,
			),
			'regex' => '/([^ ]|^){:content}[^ ]/',
			'message' => 'Operator `->` must not be be surrounded by 1 space or on its own line.',
			'tokens' => array(
				T_OBJECT_OPERATOR,
			),
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
		),
		'ternarySpacing' => array(
			'relativeTokens' => array(
				'before' => 3,
				'length' => 7,
			),
			'fullLineRegex' => '/^\s+(case|default)(.*):$/',
			'regex' => '/(([^ ] (\?:|\?|:) [^ ])|(:$)|[^:]:\n)/',
			'message' => 'Operator {:content} must be surrounded by spaces.',
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
	public function apply($testable, array $config = array()) {
		$tokens = $testable->tokens();

		foreach ($this->inspector as $inspector) {
			if (isset($inspector['tokens'])) {
				$byToken = $testable->findAll($inspector['tokens']);
			} else {
				$byToken = array();
			}
			if (isset($inspector['content'])) {
				$byContent = $testable->findAllContent($inspector['content']);
			} else {
				$byContent = array();
			}
			foreach (array_merge($byToken, $byContent) as $id) {
				$token = $tokens[$id];
				$isPHP = $testable->isPHP($token['line']);

				if ($isPHP && empty($token['isString'])) {
					$pattern = Text::insert($inspector['regex'], array(
						'content' => preg_quote($token['content'], "/"),
					));
					$firstId = $id - $inspector['relativeTokens']['before'];
					$firstId = ($firstId < 0) ? 0 : $firstId;
					$length = $inspector['relativeTokens']['length'];
					$inspectTokens = array_slice($tokens, $firstId, $length);
					$html = null;
					foreach ($inspectTokens as $htmlToken) {
						$html .= $htmlToken['content'];
					}
					if (preg_match($pattern, $html) === 0) {
						$this->addViolation(array(
							'message' => Text::insert($inspector['message'], $token),
							'line' => $token['line'],
						));
					}
				}
			}
		}
	}

}

?>
