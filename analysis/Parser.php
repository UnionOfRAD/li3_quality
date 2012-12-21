<?php

namespace li3_quality\analysis;

class Parser extends \lithium\analysis\Parser {

	/**
	 * Tokens that can be parent tokens. The mustInclude key has an array of
	 * content which must exist after the found parent token. The hasName key
	 * defines if this token can have a name. The parents array is a dependency
	 * and if none of these are the tokens immediate parents they are not a
	 * parent either.
	 *
	 * @var array
	 */
	protected static $_parentTokens = array(
		T_CLASS => array(
			'mustInclude' => array('{'),
			'hasName' => true,
			'parents' => array(),
		),
		T_IF => array(
			'mustInclude' => array('{', ':'),
			'hasName' => false,
			'parents' => array(),
		),
		T_ELSE => array(
			'mustInclude' => array('{', ':'),
			'hasName' => false,
			'parents' => array(),
		),
		T_ELSEIF => array(
			'mustInclude' => array('{', ':'),
			'hasName' => false,
			'parents' => array(),
		),
		T_FOR => array(
			'mustInclude' => array('{', ':'),
			'hasName' => false,
			'parents' => array(),
		),
		T_FOREACH => array(
			'mustInclude' => array('{', ':'),
			'hasName' => false,
			'parents' => array(),
		),
		T_FUNCTION => array(
			'mustInclude' => array('{', ';'),
			'hasName' => true,
			'parents' => array(),
		),
		T_INTERFACE => array(
			'mustInclude' => array('{'),
			'hasName' => true,
			'parents' => array(),
		),
		T_SWITCH => array(
			'mustInclude' => array('{', ':'),
			'hasName' => false,
			'parents' => array(),
		),
		T_TRY => array(
			'mustInclude' => array('{'),
			'hasName' => false,
			'parents' => array(),
		),
		T_CATCH => array(
			'mustInclude' => array('{'),
			'hasName' => false,
			'parents' => array(),
		),
		T_WHILE => array(
			'mustInclude' => array('{', ':', ';'),
			'hasName' => false,
			'parents' => array(),
		),
		T_DO => array(
			'mustInclude' => array('while'),
			'hasName' => false,
			'parents' => array(),
		),
		T_DECLARE => array(
			'mustInclude' => array('{', ':'),
			'hasName' => false,
			'parents' => array(),
		),
		T_VARIABLE => array(
			'mustInclude' => array(';'),
			'hasName' => true,
			'parents' => array(
				T_CLASS,
			),
		),
	);

	/**
	 * These tokens have parents that will exist after they are declared
	 *
	 * @var array
	 */
	protected static $_beforeParents = array(
		T_CONST,
		T_ABSTRACT,
		T_PUBLIC,
		T_PRIVATE,
		T_PROTECTED,
		T_STATIC,
		T_DOC_COMMENT,
	);

	/**
	 * These tokens can end blocks
	 *
	 * @var  array
	 */
	protected static $_endingBlocks = array(
		T_ENDDECLARE,
		T_ENDFOR,
		T_ENDFOREACH,
		T_ENDIF,
		T_ENDSWITCH,
		T_ENDWHILE,
	);

	/**
	 * Analyzes $token and determines if it is a suitable parent by checking
	 * for it in the $_parentTokens list, and checking its dependencies.
	 *
	 * @param  array $token  The token you are analyzing
	 * @param  array $tokens The array of the currently created tokens
	 * @return bool          If this token is a parent or not
	 */
	protected static function _isParent(&$token, &$tokens) {
		if (!isset(static::$_parentTokens[$token['id']])) {
			return false;
		}
		$requiredParents = static::$_parentTokens[$token['id']]['parents'];
		$parentId = $token['parent'];
		$isDecoy = false;
		$hasRequiredParents = empty($requiredParents);
		if (isset($tokens[$parentId])) {
			$parentToken = $tokens[$parentId]['id'];
			if (!$hasRequiredParents) {
				$hasRequiredParents = in_array($parentToken, $requiredParents);
			}
			if ($token['id'] === T_IF) {
				$tokenId = array_search($token, $tokens);
				$correctParent = $parentToken === T_ELSE;
				$correctSpacing = $tokenId - $parentId === 2;
				$correctWhitespace = $tokens[$tokenId - 1]['id'] === T_WHITESPACE;
				$isDecoy = $correctParent && $correctSpacing && $correctWhitespace;
			}
		}
		return $hasRequiredParents && !$isDecoy;
	}

	/**
	 * The must include variable tells us that this parent goes at least this far, and which gives
	 * us a good checking point for ending non-block parents.
	 *
	 * @param  array $token  The token you are analyzing
	 * @param  array $tokens The array of the currently created tokens
	 * @return int           The id of the last guaranteed child of $token
	 */
	protected static function _mustInclude(&$token, &$tokens) {
		$lastSafeId = 0;
		$total = count($tokens);
		$tokenId = array_search($token, $tokens) + 1;
		$content = static::$_parentTokens[$token['id']]['mustInclude'];
		if ($token['id'] === T_DO) {
			return static::_mustIncludeDo($token, $tokens);
		}
		for ($id = $tokenId; $id < $total; $id++) {
			if ($tokens[$id]['line'] === $token['line']) {
				$lastSafeId = $id;
			}
			$nonVar = $tokens[$id]['id'] !== T_VARIABLE;
			$foundParent = isset(static::$_parentTokens[$tokens[$id]['id']]);
			if ($nonVar && $foundParent) {
				return $lastSafeId;
			} elseif (in_array($tokens[$id]['content'], $content)) {
				return $id;
			}
		}
		return $lastSafeId;
	}

	/**
	 * T_DO tokens are tricky to guess their end and require seperate logic.
	 * Here we simply track the brackets, the first semicolon after the level
	 * is back to zero is the ending token.
	 *
	 * @param  array $token  The token you are analyzing
	 * @param  array $tokens The array of the currently created tokens
	 * @return int           The id of the last guaranteed child of $token
	 */
	protected static function _mustIncludeDo(&$token, &$tokens) {
		$total = count($tokens);
		$tokenId = array_search($token, $tokens) + 1;
		$level = 0;
		$foundBracket = false;
		for ($id = $tokenId; $id < $total; $id++) {
			if ($tokens[$id]['content'] === '{') {
				$level++;
				$foundBracket = true;
			} elseif ($tokens[$id]['content'] === '}') {
				$level--;
			} elseif ($level === 0 && $foundBracket && $tokens[$id]['content'] === ';') {
				return $id;
			}
		}
		return -1;
	}

	/**
	 * Will find the label of the given token
	 *
	 * @param  array $token  The token you are analyzing
	 * @param  array $tokens The array of the currently created tokens
	 * @return string
	 */
	protected static function _findLabel(&$token, &$tokens) {
		$isParent = isset(static::$_parentTokens[$token['id']]);
		if ($isParent && static::$_parentTokens[$token['id']]['hasName']) {
			if ($token['id'] === T_VARIABLE) {
				return substr($token['content'], 1);
			}
			$total = count($tokens);
			foreach (range(array_search($token, $tokens), $total) as $key) {
				if ($tokens[$key]['id'] === T_STRING) {
					return $tokens[$key]['content'];
				} elseif (in_array($tokens[$key]['content'], array('(', '{'))) {
					return 'anonymous';
				}
			}
		}
		return null;
	}

	/**
	 * Will determine if this token can be queued before before the parent or not.
	 *
	 * @param  array $token  The token you are analyzing
	 * @param  array $tokens The array of the currently created tokens
	 * @return bool
	 */
	protected static function _canQueue(&$token, &$tokens) {
		if ($token['id'] === T_STATIC) {
			$tokenId = array_search($token, $tokens);
			if ($tokens[$tokenId + 1]['id'] === T_WHITESPACE) {
				return true;
			}
			return false;
		} elseif ($token['id'] === T_DOC_COMMENT) {
			$tokenId = array_search($token, $tokens);
			$height = count(preg_split('/\r\n|\r|\n/', $token['content']));
			$parentLine = $token['line'] + $height;
			$nextParent = false;
			foreach (range($tokenId, count($tokens) - 1) as $id) {
				$possibleParent = $tokens[$id];
				if ($possibleParent['line'] > $parentLine) {
					return false;
				} elseif (in_array($possibleParent['id'], array(T_CLASS, T_FUNCTION, T_VARIABLE))) {
					return true;
				}
			}
			return false;
		}
		return in_array($token['id'], static::$_beforeParents);
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
	 * @return array An array of tokens in the supplied source code.
	 */
	public static function tokenize($code, array $options = array()) {
		$tokens = parent::tokenize($code, $options);
		$level = 0;
		$queue = $currentParent = $mustInclude = -1;
		$total = count($tokens);
		$maxLevel = 0;
		$curlyOpen = false;
		foreach ($tokens as $tokenId => &$token) {
			if (isset($tokens[$currentParent])) {
				$tokens[$currentParent]['children'][] = $tokenId;
			}
			$token['parent'] = $currentParent;
			$token['level'] = $level;
			$token['children'] = array();
			$token['label'] = static::_findLabel($token, $tokens);

			if ($maxLevel > 0 && $mustInclude <= $tokenId) {
				if ($token['content'] === '}' || in_array($token['id'], self::$_endingBlocks)) {
					if ($curlyOpen) {
						$curlyOpen = false;
					} else {
						$level--;
					}
				}
				if (isset($tokens[$currentParent])) {
					$closeByBrackets = $tokens[$currentParent]['level'] >= $level;
					$closeByNoBlock = $mustInclude === $tokenId && $token['content'] === ';';
					if ($closeByBrackets || $closeByNoBlock) {
						$originalToken = $tokens[$currentParent]['id'];
						$currentParent = $tokens[$currentParent]['parent'];
						$currentParentId = T_WHITESPACE;
						if (isset($tokens[$currentParent]['id'])) {
							$currentParentId = $tokens[$currentParent]['id'];
						}
						if ($originalToken === T_WHILE && $currentParentId === T_DO) {
							$currentParent = $tokens[$currentParent]['parent'];
							$level--;
						}
						$mustInclude = 0;
						if (isset($tokens[$currentParent]['mustInclude'])) {
							$mustInclude = $tokens[$currentParent]['mustInclude'];
						}
						if ($closeByNoBlock) {
							$level--;
						}
					}
				}
			}

			if (static::_isParent($token, $tokens)) {
				$currentParent = $tokenId;
				$mustInclude = static::_mustInclude($token, $tokens);
				$token['mustInclude'] = $mustInclude;
				if ($queue !== -1) {
					foreach (range($queue, $tokenId - 1) as $key) {
						$tokens[$key]['parent'] = $currentParent;
						$tokens[$key]['level'] = $level;
						$token['children'][] = $key;
					}
				}
				$level++;
				$queue = -1;
				$maxLevel = max($level, $maxLevel);
			} elseif ($queue === -1 && static::_canQueue($token, $tokens)) {
				$queue = $tokenId;
			} elseif ($token['id'] === T_CURLY_OPEN) {
				$curlyOpen = true;
			}
		}
		if ($queue !== -1 || $level !== 0) {
			$smallTokens = array_slice($tokens, 0, 20);
			$data = print_r(compact('queue', 'level', 'tokens'), true);
			throw new \LogicException('A parse error has been encountered.' . $data);
		}
		return $tokens;
	}
}

?>