<?php

namespace li3_quality\analysis;

use li3_quality\analysis\ParserExepction;

class Parser extends \lithium\analysis\Parser {

	/**
	 * Tokens that can be parent tokens.
	 *
	 * @var array
	 */
	protected static $_parentTokens = array(
		T_CLASS => array(
			'endingTokens' => array(T_ENDFOR),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_IF => array(
			'endingTokens' => array(T_ENDFOR),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_ELSE => array(
			'endingTokens' => array(T_ENDFOR),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_ELSEIF => array(
			'endingTokens' => array(T_ENDFOR),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_FOR => array(
			'endingTokens' => array(T_ENDFOR),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_FOREACH => array(
			'endingTokens' => array(T_ENDFOREACH),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_FUNCTION => array(
			'endingTokens' => array(),
			'endingContent' => array('}', ';'),
			'parents' => array(),
		),
		T_INTERFACE => array(
			'endingTokens' => array(),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_SWITCH => array(
			'endingTokens' => array(),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_TRY => array(
			'endingTokens' => array(),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_CATCH => array(
			'endingTokens' => array(),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_WHILE => array(
			'endingTokens' => array(T_ENDWHILE),
			'endingContent' => array('}', ';'),
			'parents' => array(),
		),
		T_DO => array(
			'endingTokens' => array(),
			'endingContent' => array('}'),
			'parents' => array(),
		),
		T_DECLARE => array(
			'endingTokens' => array(),
			'endingContent' => array(';', '}'),
			'parents' => array(),
		),
		T_VARIABLE => array(
			'endingTokens' => array(),
			'endingContent' => array(';'),
			'parents' => array(
				T_CLASS,
			),
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
			for ($key = $tokenId;$key <= $total;$key++) {
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
		for ($key = $tokenId;$key <= $total;$key++) {
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
		for ($key = $tokenId;$key <= $total;$key++) {
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
		for ($key = $tokenId - 1;$key >= 0;$key--) {
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
		$tokens = parent::tokenize($code, $options);
		$currentParent = -1;
		$brackets = $curlyBrackets = $level = 0;
		$lineCache = $typeCache = array();
		foreach ($tokens as $tokenId => $token) {
			if ($token['id'] !== T_ENCAPSED_AND_WHITESPACE) {
				if ($token['content'] === '{') {
					$curlyBrackets++;
				} elseif ($token['content'] === '}') {
					$curlyBrackets--;
				} elseif ($token['content'] === '(') {
					$brackets++;
				} elseif ($token['content'] === ')') {
					$brackets--;
				}
			}

			if (!isset($lineCache[$token['line']])) {
				$lineCache[$token['line']] = array();
			}
			$lineCache[$token['line']][] = $tokenId;

			if (!isset($typeCache[$token['id']])) {
				$typeCache[$token['id']] = array();
			}
			$typeCache[$token['id']][] = $tokenId;

			$tokens[$tokenId]['level'] = $level;
			$tokens[$tokenId]['brackets'] = $brackets;
			$tokens[$tokenId]['curlyBrackets'] = $curlyBrackets;
			$tokens[$tokenId]['parent'] = $currentParent;
			$tokens[$tokenId]['children'] = array();
			if (isset($tokens[$currentParent])) {
				$tokens[$currentParent]['children'][] = $tokenId;
			}

			$parent = static::_isEndOfParent($tokenId, $currentParent, $tokens);
			if ($parent !== false) {
				$level--;
				$currentParent = $parent;
			} elseif (static::_isParent($tokenId, $tokens)) {
				$level++;
				$currentParent = $tokenId;
			}
		}
		if ($level !== 0 || $curlyBrackets !== 0 || $brackets !== 0) {
			$smallTokens = array_slice($tokens, 0, 20);
			$exception = new ParserException('A parse error has been encountered.');
			$exception->parserData = compact('level', 'curlyBrackets', 'brackets', 'tokens');
			throw $exception;
		}
		return compact('tokens', 'lineCache', 'typeCache');
	}

	/**
	 * Will determine if this is the end of the current parent.
	 *
	 * @param  int   $tokenId  The tokenId you are analyzing
	 * @param  int   $parentId The tokenId of the currentParent
	 * @param  array $tokens   The array of the currently created tokens
	 * @return int|bool        Will either return `false` or the id of the new currentParent.
	 */
	protected static function _isEndOfParent($tokenId, $parentId, array $tokens) {
		if (!isset($tokens[$parentId])) {
			return false;
		}
		$token = $tokens[$tokenId];
		$parent = $tokens[$parentId];
		if ($tokens[$tokenId]['curlyBrackets'] !== $tokens[$parentId]['curlyBrackets']) {
			return false;
		}
		$endingTokens = static::$_parentTokens[$parent['id']]['endingTokens'];
		$endingContent = static::$_parentTokens[$parent['id']]['endingContent'];
		$hasEndingTokens = in_array($token['id'], $endingTokens);
		$hasEndingContent = in_array($token['content'], $endingContent);
		if ($hasEndingTokens || $hasEndingContent) {
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
		$requiredParents = static::$_parentTokens[$token['id']]['parents'];
		$parentId = $tokens[$tokenId]['parent'];
		$isDecoy = false;
		$hasRequiredParents = empty($requiredParents);
		if (isset($tokens[$parentId])) {
			$parentToken = $tokens[$parentId]['id'];
			if (!$hasRequiredParents) {
				$hasRequiredParents = in_array($parentToken, $requiredParents);
			}
			if ($token['id'] === T_IF) {
				$correctParent = $parentToken === T_ELSE;
				$correctSpacing = $tokenId - $parentId === 2;
				$correctWhitespace = $tokens[$tokenId - 1]['id'] === T_WHITESPACE;
				$isDecoy = $correctParent && $correctSpacing && $correctWhitespace;
			}
		}
		return $hasRequiredParents && !$isDecoy;
	}

}

?>