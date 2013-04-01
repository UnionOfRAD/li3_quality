<?php

namespace li3_quality\analysis;

use li3_quality\analysis\ParserException;

define('T_DOLLAR_CURLY_BRACES', 500);
define('T_ARRAY_OPEN', 501);
define('T_SHORT_ARRAY_OPEN', 502);
define('T_START_DOUBLE_QUOTE', 503);
define('T_END_DOUBLE_QUOTE', 504);
define('T_START_BRACKET', 505);

class Parser extends \lithium\analysis\Parser {

	/**
	 * The bracket checksum value for tokenization.
	 *
	 * @var array
	 */
	protected static $_bracketsChecksum = 0;

	/**
	 * Tokens that can be parent tokens.
	 *
	 * @var array
	 */
	protected static $_parentTokens = array(
		T_CLASS => array(
			'endingTokens' => array('}' => '{'),
			'nestOn' => array('{')
		),
		T_IF => array(
			'endingTokens' => array(
				'T_ENDIF' => ':',
				'T_ELSE' => true,
				'T_ELSEIF' => true,
				'}' => '{'
			),
			'nestOn' => array('{', ':')
		),
		T_ELSE => array(
			'endingTokens' => array(
				'T_ENDIF' => ':',
				'T_ELSE' => true,
				'T_ELSEIF' => true,
				'}' => '{'
			),
			'nestOn' => array('{', ':')
		),
		T_ELSEIF => array(
			'endingTokens' => array(
				'T_ENDIF' => ':',
				'T_ELSE' => true,
				'T_ELSEIF' => true,
				'}' => '{'
			),
			'endingContent' => array('}'),
			'nestOn' => array('{', ':')
		),
		T_FOR => array(
			'endingTokens' => array(
				'T_ENDFOR' => ':',
				'}' => '{'
			),
			'nestOn' => array('{', ':')
		),
		T_FOREACH => array(
			'endingTokens' => array(
				'T_ENDFOREACH' => ':',
				'}' => '{'
			),
			'nestOn' => array('{', ':')
		),
		T_FUNCTION => array(
			'endingTokens' => array('}' => '{', ';' => true),
			'nestOn' => array('{')
		),
		T_INTERFACE => array(
			'endingTokens' => array('}' => '{'),
			'nestOn' => array('{')
		),
		T_SWITCH => array(
			'endingTokens' => array('}' => '{'),
			'nestOn' => array('{', ':')
		),
		T_CASE => array(
			'endingTokens' => array(
				'T_CASE' => ':',
				'T_BREAK' => ':',
				'T_DEFAULT' => ':'
			),
			'nestOn' => array(':')
		),
		T_DEFAULT => array(
			'endingTokens' => array(
				'T_BREAK' => ':'
			),
			'nestOn' => array(':')
		),
		T_TRY => array(
			'endingTokens' => array('}' => '{'),
			'nestOn' => array('{')
		),
		T_CATCH => array(
			'endingTokens' => array('}' => '{'),
			'nestOn' => array('{')
		),
		T_WHILE => array(
			'endingTokens' => array(
				'T_ENDWHILE' => ':',
				'}' => '{',
				';' => true
			),
			'nestOn' => array('{')
		),
		T_DO => array(
			'endingTokens' => array('}' => '{'),
			'nestOn' => array('{')
		),
		T_DECLARE => array(
			'endingTokens' => array(
				'T_ENDDECLARE' => ':',
				'}' => '{',
				';' => true
			),
			'nestOn' => array('{', ':')
		),
		T_ARRAY_OPEN => array(
			'endingTokens' => array(')' => 'T_ARRAY_OPEN'),
			'nestOn' => true
		),
		T_SHORT_ARRAY_OPEN => array(
			'endingTokens' => array(']' => 'T_SHORT_ARRAY_OPEN'),
			'nestOn' => true
		),
		T_START_BRACKET => array(
			'endingTokens' => array(')' => 'T_START_BRACKET'),
			'nestOn' => true
		),
		T_DOLLAR_CURLY_BRACES => array(
			'endingTokens' => array('}' => 'T_DOLLAR_CURLY_BRACES'),
			'nestOn' => false
		),
		T_START_HEREDOC => array(
			'endingTokens' => array('T_END_HEREDOC' => 'T_START_HEREDOC'),
			'nestOn' => false
		),
		T_START_DOUBLE_QUOTE => array(
			'endingTokens' => array('T_END_DOUBLE_QUOTE' => 'T_START_DOUBLE_QUOTE'),
			'nestOn' => false
		),
	);

	/**
	 * These tokens are considered modifiers
	 *
	 * @var  array
	 */
	public static $modifiers = array(
		T_CONST,
		T_PUBLIC,
		T_PROTECTED,
		T_PRIVATE,
		T_ABSTRACT,
		T_STATIC,
	);

	/**
	 * Will find the label of the given token.
	 *
	 * @param  array $tokenId The tokenId you are analyzing
	 * @param  array $tokens  The array of the currently created tokens
	 * @return string
	 */
	public static function label($tokenId, array $tokens) {
		$token = $tokens[$tokenId];
		$hasName = in_array($token['id'], array(
			T_FUNCTION,
			T_CLASS,
			T_INTERFACE,
			T_VARIABLE,
		));
		if ($hasName) {
			if ($token['id'] === T_VARIABLE) {
				return substr($token['content'], 1);
			}
			$total = count($tokens);
			for ($key = $tokenId; $key <= $total; $key++) {
				if ($tokens[$key]['id'] === T_STRING) {
					return $tokens[$key]['content'];
				} elseif (in_array($tokens[$key]['content'], array('(', '{', ':'))) {
					break;
				}
			}
		}
		return null;
	}

	/**
	 * Will return the parameters of a given token.
	 *
	 * @param  array $tokenId The tokenId you are analyzing
	 * @param  array $tokens  The array of the currently created tokens
	 * @return array
	 */
	public static function parameters($tokenId, array $tokens) {
		$params = array();
		if ($tokens[$tokenId]['id'] !== T_FUNCTION) {
			throw new \Exception('Cannot call params on non function');
		}
		$foundOpen = false;
		$total = count($tokens);
		for ($key = $tokenId; $key <= $total; $key++) {
			$token = $tokens[$key];
			if ($foundOpen) {
				if ($token['content'] === ')') {
					break;
				} elseif ($token['id'] === T_VARIABLE) {
					$params[] = $key;
				}
			} elseif ($token['content'] === '(') {
				$foundOpen = true;
			}
		}
		return $params;
	}

	/**
	 * Will determine if the current token is an anonymous funciton/closure.
	 *
	 * @param  array $tokenId The tokenId you are analyzing
	 * @param  array $tokens  The array of the currently created tokens
	 * @return bool
	 */
	public static function closure($tokenId, array $tokens) {
		if ($tokens[$tokenId]['id'] !== T_FUNCTION) {
			throw new \Exception('Cannot call params on non function');
		}
		$total = count($tokens);
		for ($key = $tokenId; $key <= $total; $key++) {
			if ($tokens[$key]['id'] === T_STRING) {
				return false;
			} elseif (in_array($tokens[$key]['content'], array('(', '{', ':'))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Will return a list of all the modifiers for a given token.
	 *
	 * @param  array $tokenId The tokenId you are analyzing
	 * @param  array $tokens  The array of the currently created tokens
	 * @return array          An array of tokenId's
	 */
	public static function modifiers($tokenId, array $tokens) {
		if (!in_array($tokens[$tokenId]['id'], array(T_CLASS, T_FUNCTION, T_VARIABLE))) {
			$token = print_r($tokens[$tokenId], true);
			throw new \Exception('Cannot call modifiers on non class/function/variable' . $token);
		}
		$modifiers = array();
		for ($key = $tokenId - 1; $key >= 0; $key--) {
			$token = $tokens[$key];
			if ($token['id'] === T_WHITESPACE) {
				continue;
			} elseif (in_array($token['id'], static::$modifiers)) {
				$modifiers[] = $key;
			} else {
				break;
			}
		}
		return $modifiers;
	}

	/**
	 * Adds more token information than the base lithium tokenizer such as name,
	 * parent, and children.
	 *
	 * @param string $code Source code to be tokenized.
	 * @param array $options Options consists of:
	 *        -'wrap': Boolean indicating whether or not to wrap the supplied
	 *          code in PHP tags.
	 *        -'ignore': An array containing PHP language tokens to ignore.
	 *        -'include': If supplied, an array of the only language tokens
	 *         to include in the output.
	 * @return array An array of extracted information from the supplied source code:
	 *         - lineCache: token ids indexed by line number
	 *         - typeCache: token ids indexed by token type
	 *         - meta: parsing information (level, etc.) indexed by token id
	 *         - relationships: parent and child relations (token ids) indexed by token id
	 */
	public static function tokenize($code, array $options = array()) {
		$options += array('wrap' => true);
		$tokens = static::_tokenize(parent::tokenize($code, $options));
		static::$_bracketsChecksum = 0;
		$curParent = -1;
		$brackets = $curlyBrackets = $squareBrackets = 0;
		$level = $needNestUp = $nestLine = $nestLevel = 0;
		$nestLog = $lineCache = $typeCache = array();
		$inString = false;
		$inPhp = $options['wrap'] ? true : false;

		foreach ($tokens as $tokenId => $token) {

			$isString = (
				$token['id'] === T_CONSTANT_ENCAPSED_STRING ||
				$token['id'] === T_ENCAPSED_AND_WHITESPACE
			);

			$lineCache[$token['line']][] = $tokenId;
			if ($isString) {
				$carriageReturn = substr_count($token['content'], "\n");
				for ($i = 1; $i <= $carriageReturn; $i++) {
					$lineCache[$token['line'] + $i][] = $tokenId;
				}
			}
			$typeCache[$token['id']][] = $tokenId;

			if ($token['id'] === T_CLOSE_TAG) {
				$needNestUp = 0;
				$nestLevel = 0;
				$inPhp = false;
			}

			if ($token['id'] === T_OPEN_TAG) {
				$inPhp = true;
			}

			if (!$inPhp) {
				continue;
			}

			if ($token['id'] === T_END_DOUBLE_QUOTE || $token['id'] === T_END_HEREDOC) {
				$inString = false;
			}

			if ($needNestUp > 0) {
				$status = $nestLog[$needNestUp];
				if (!$status['founded'] && in_array($token['content'], $status['nestOn'])) {
					$nestLog[$needNestUp]['founded'] = true;
				}
				if ($token['line'] > $nestLine && $status['founded'] && !$status['applied']) {
					$nestLevel++;
					$nestLog[$needNestUp]['applied'] = true;
				}
			}

			$tokens[$tokenId] = static::_checksum($tokens[$tokenId], $isString || $inString);
			$tokens[$tokenId]['level'] = $level;
			$tokens[$tokenId]['parent'] = $curParent;
			$tokens[$tokenId]['children'] = array();
			if (isset($tokens[$curParent])) {
				$tokens[$curParent]['children'][] = $tokenId;
			}

			while (($parent = static::_isEndOfParent($tokenId, $curParent, $tokens)) !== false) {
				if (!$inString && static::$_parentTokens[$tokens[$curParent]['id']]['nestOn']) {
					$needNestUp === 0 ?: $needNestUp--;
					$nestLevel = $tokens[$curParent]['nestLevel'];
				}
				$level--;
				$curParent = $parent;
			}

			$tokens[$tokenId]['nestLevel'] = $inString ? null : $nestLevel;
			$tokens[$tokenId]['isString'] = $isString;

			if ($token['id'] === T_START_DOUBLE_QUOTE || $token['id'] === T_START_HEREDOC) {
				$inString = true;
			}

			if (static::_isParent($tokenId, $tokens)) {
				$tokens[$tokenId]['parent'] = $curParent;
				if (!$inString && $nestOn = static::$_parentTokens[$token['id']]['nestOn']) {
					$nestLine = $token['line'];
					$nestLog[++$needNestUp] = array(
						'nestOn' => $nestOn,
						'founded' => $nestOn === true ? true : false,
						'applied' => false
					);
				}
				$curParent = $tokenId;
				$level++;
			}
		}
		if ($level !== 0 || $squareBrackets !== 0 || $curlyBrackets !== 0 || $brackets !== 0) {
			$smallTokens = array_slice($tokens, 0, 20);
			$exception = new ParserException('A parse error has been encountered.');
			$exception->parserData = compact('level', 'curlyBrackets', 'brackets', 'tokens');
			throw $exception;
		}
		return compact('tokens', 'lineCache', 'typeCache');
	}

	/**
	 * Update the bracket checksum values for a token.
	 *
	 * @param  array   $token    The token.
	 * @param  boolean $isString A Flag indicating if the token is a string part.
	 * @return array The token with a valid checksum.
	 */
	protected static function _checksum(array $token, $isString) {
		$token['checksum'] = static::$_bracketsChecksum;
		if ($isString) {
			return $token;
		}
		$char = $token['content'];
		if ($char === ')' || $char === ']' || $char === '}') {
			$token['checksum'] = --static::$_bracketsChecksum;
		} elseif ($char === '(' || $char === '[' || $char === '{') {
			static::$_bracketsChecksum++;
		}
		return $token;
	}

	/**
	 * Will determine if this is the end of the current parent.
	 *
	 * @param  int   $tokenId  The tokenId you are analyzing
	 * @param  int   $parentId The tokenId of the curParent
	 * @param  array $tokens   The array of the currently created tokens
	 * @return int|bool        Will either return `false` or the id of the new curParent.
	 */
	protected static function _isEndOfParent($tokenId, $parentId, array $tokens) {
		if (!isset($tokens[$parentId])) {
			return false;
		}

		$diff = $tokens[$tokenId]['checksum'] - $tokens[$parentId]['checksum'];

		if ($diff > 0) {
			return false;
		}

		$token = $tokens[$tokenId];
		$parent = $tokens[$parentId];
		$endingTokens = static::$_parentTokens[$parent['id']]['endingTokens'];
		if (isset($endingTokens[$token['name']]) || ($diff < 0 && $token['name'] === '}')) {
			return $tokens[$parentId]['parent'];
		}

		return false;
	}

	/**
	 * Analyzes $token and determines if it is a suitable parent by checking
	 * for it in the $_parentTokens list, and checking its dependencies.
	 *
	 * @param  array $tokenId The tokenId you are analyzing
	 * @param  array $tokens The array of the currently created tokens
	 * @return bool          If this token is a parent or not
	 */
	protected static function _isParent($tokenId, array $tokens) {
		$token = $tokens[$tokenId];
		if (!isset(static::$_parentTokens[$token['id']])) {
			return false;
		}
		return true;
	}

	/**
	 * Adding extra tokens for quality rules
	 *
	 * - Add some new token IDs
	 * - Merge T_ELSE + T_WHITESPACE(s) + T_IF to an unique T_ELSEIF token
	 *
	 * @param  array $tokens The array of tokens
	 * @return array The array of extended tokens
	 */
	protected static function _tokenize(array $tokens) {
		$doubleQuote = false;
		$results = array();
		$cpt = 0;
		foreach ($tokens as $tokenId => $token) {
			if ($token['content'] === '"') {
				if (!$doubleQuote) {
					$token['id'] = T_START_DOUBLE_QUOTE;
					$token['name'] = 'T_START_DOUBLE_QUOTE';
				} else {
					$token['id'] = T_END_DOUBLE_QUOTE;
					$token['name'] = 'T_END_DOUBLE_QUOTE';
				}
				$doubleQuote = !$doubleQuote;
			}

			$isCurlyBrace = (
				$token['content'] === '$' &&
				isset($tokens[$tokenId + 1]) &&
				$tokens[$tokenId + 1]['content'] === '{'
			);
			if ($isCurlyBrace) {
				$token['id'] = T_DOLLAR_CURLY_BRACES;
				$token['name'] = 'T_DOLLAR_CURLY_BRACES';
			}

			$isArray = (
				$token['content'] === '(' &&
				$previousTokenId !== null &&
				$tokens[$previousTokenId]['id'] === T_ARRAY
			);
			if ($isArray) {
				$token['id'] = T_ARRAY_OPEN;
				$token['name'] = 'T_ARRAY_OPEN';
			}

			$isShortArray = (
				$token['content'] === '[' &&
				$previousTokenId !== null && (
					$tokens[$previousTokenId]['id'] !== T_VARIABLE &&
					$tokens[$previousTokenId]['id'] !== T_ENCAPSED_AND_WHITESPACE
				)
			);
			if ($isShortArray) {
				$token['id'] = T_SHORT_ARRAY_OPEN;
				$token['name'] = 'T_SHORT_ARRAY_OPEN';
			}

			if ($token['content'] === '(' && !$token['id']) {
				$token['id'] = T_START_BRACKET;
				$token['name'] = 'T_START_BRACKET';
			}

			if ($token['id'] !== T_WHITESPACE) {
				$previousTokenId = $tokenId;
			}

			if ($token['id'] === T_IF) {
				$i = $cpt;
				$spaces = '';
				while ($i-- > 0 && $results[$i - 1]['id'] === T_WHITESPACE) {
					$spaces = $results[$i - 1]['content'] . $spaces;
				}
				if (--$i >= 0 && $results[$i]['id'] === T_ELSE) {
					$token['id'] = T_ELSEIF;
					$token['name'] = 'T_ELSEIF';
					$token['content'] = $results[$i]['content'] . $spaces . $token['content'];
					$cpt = $i;
				}
			}

			$results[$cpt++] = $token;
		}

		return $results;
	}
}

?>