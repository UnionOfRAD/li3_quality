<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

use lithium\core\Libraries;
use li3_quality\analysis\Parser;

class Testable extends \lithium\core\Object {

	/**
	 * The source code of the testable class.
	 *
	 * @var string
	 */
	protected $_source = null;

	/**
	 * Contains the source code as an array for each line.
	 *
	 * @var array
	 */
	protected $_lines = null;

	/**
	 * Contains the class tokens.
	 *
	 * @var array
	 */
	protected $_tokens = null;

	/**
	 * Contains the config of the testable class.
	 *
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Contains lineCache for tokens.
	 *
	 * @var array
	 */
	protected $_lineCache = null;

	/**
	 * Contains typeCache for tokens.
	 *
	 * @var array
	 */
	protected $_typeCache = null;

	/**
	 * Locates the file and reads its source code.
	 */
	public function __construct(array $config = array()) {
		$this->_config = $config + array(
			'wrap' => false,
		);
		$path = Libraries::path($config['path']);
		$this->_config['path'] = $path;
		$this->_source = file_get_contents($path);
	}

	/**
	 * Config accessor method.
	 *
	 * @param   string $param The configuration key
	 * @return  mixed
	 */
	public function config($param = null) {
		if ($param === null) {
			return $this->_config;
		}
		return isset($this->_config[$param]) ? $this->_config[$param] : null;
	}

	/**
	 * Accessor method for the source code.
	 *
	 * @return  string
	 */
	public function source() {
		return $this->_source;
	}

	/**
	 * Accessor method for the tokens.
	 *
	 * It only tokenizes the file when the tokens are actually needed,
	 * which increases performance in cases where you only need to
	 * apply regex checks on the file. The result is cached afterwards.
	 *
	 * The PHP version check is in there, because on 5.3.6 (and maybe until 5.3.9),
	 * when using an SplFixed array (which is faster), the app segfaults.
	 *
	 * @return  array
	 */
	public function tokens() {
		if ($this->_tokens === null) {
			$tokenized = Parser::tokenize($this->source(), $this->_config);
			$this->_tokens = $tokenized['tokens'];
			$this->_lineCache = $tokenized['lineCache'];
			$this->_typeCache = $tokenized['typeCache'];
		}
		return $this->_tokens;
	}

	/**
	 * Accessor method for the tokens lineCache.
	 *
	 * @return  array
	 */
	public function lineCache() {
		if ($this->_lineCache === null) {
			$this->tokens();
		}
		return $this->_lineCache;
	}

	/**
	 * Accessor method for the tokens typeCache.
	 *
	 * @return  array
	 */
	public function typeCache() {
		if ($this->_typeCache === null) {
			$this->tokens();
		}
		return $this->_typeCache;
	}

	/**
	 * Will find the next content
	 *
	 * @param  array           $types The types you wish to find ('$foo', '{', ...)
	 * @param  integer|array   $range Where you want to start, or an array of items to search
	 * @return integer|boolean        The index of the next $type or false if nothing is found
	 */
	public function findNextContent(array $types, $range = 0) {
		$tokens = $this->tokens();
		if (is_array($range)) {
			$start = 0;
			$end = count($range);
		} else {
			$start = $range;
			$end = count($tokens);
		}
		for ($idx = $start;$idx < $end;$idx++) {
			$id = is_array($range) ? $range[$idx] : $idx;
			$token = isset($tokens[$id]);
			if ($token && in_array($tokens[$id]['content'], $types)) {
				return $id;
			}
		}
		return false;
	}

	/**
	 * Will find the previous content
	 *
	 * @param  array           $types The types you wish to find ('$foo', '{', ...)
	 * @param  integer|array   $range Where you want to start, or an array of items to search
	 * @return integer|boolean        The index of the next $type or false if nothing is found
	 */
	public function findPrevContent(array $types, $range = 0) {
		$tokens = $this->tokens();
		if (is_array($range)) {
			$end = count($range) - 1;
		} else {
			$end = $range;
		}
		for ($idx = $end;$idx >= 0;$idx--) {
			$id = is_array($range) ? $range[$idx] : $idx;
			$token = isset($tokens[$id]);
			if ($token && in_array($tokens[$id]['content'], $types)) {
				return $id;
			}
		}
		return false;
	}

	/**
	 * Will find the next token
	 *
	 * @param  array           $types The types you wish to find (T_VARIABLE, T_FUNCTION, ...)
	 * @param  integer|array   $range Where you want to start, or an array of items to search
	 * @return integer|boolean        The index of the next $type or false if nothing is found
	 */
	public function findNext(array $types, $range = 0) {
		$tokens = $this->tokens();
		if (is_array($range)) {
			$start = 0;
			$end = count($range);
		} else {
			$start = $range;
			$end = count($tokens);
		}
		for ($idx = $start;$idx < $end;$idx++) {
			$id = is_array($range) ? $range[$idx] : $idx;
			if (isset($tokens[$id]) && in_array($tokens[$id]['id'], $types)) {
				return $id;
			}
		}
		return false;
	}

	/**
	 * Will find the previous token
	 *
	 * @param  array           $types The types you wish to find (T_VARIABLE, T_FUNCTION, ...)
	 * @param  integer|array   $range Where you want to start, or an array of items to search
	 * @return integer|boolean        The index of the next $type or false if nothing is found
	 */
	public function findPrev(array $types, $range = 0) {
		$tokens = $this->tokens();
		if (is_array($range)) {
			$end = count($range) - 1;
		} else {
			$end = $range;
		}
		for ($idx = $end;$idx >= 0;$idx--) {
			$id = is_array($range) ? $range[$idx] : $idx;
			if (isset($tokens[$id]) && in_array($tokens[$id]['id'], $types)) {
				return $id;
			}
		}
		return false;
	}

	/**
	 * Will find all matching tokens
	 *
	 * @param  array           $types The types you wish to find (T_VARIABLE, T_FUNCTION, ...)
	 * @param  integer|array   $range Where you want to start, or an array of items to search
	 * @return array           An array of found item ids, or an empty array when nothing is found
	 */
	public function findAll(array $types, $range = NULL) {
		$typeCache = $this->typeCache();
		$ids = array();
		foreach ($types as $type) {
			if (isset($typeCache[$type])) {
				$ids = array_merge($ids, $typeCache[$type]);
			}
		}

		if (!is_int($range) && !is_array($range)) {
			return $ids;
		}

		$filtered = array();
		foreach ($ids as $id) {
			$inRange = is_array($range) && in_array($id, $range);
			$belowRange = is_int($range) && $id >= $range;
			if ($inRange || $belowRange) {
				$filtered[] = $id;
			}
		}
		return $filtered;
	}

	/**
	 * Will find all matching content
	 *
	 * @param  array           $types The types you wish to find ('$foo', '{', ...)
	 * @param  integer|array   $range Where you want to start, or an array of items to search
	 * @return array           An array of found item ids, or an empty array when nothing is found
	 */
	public function findAllContent(array $types, $range = 0) {
		$tokens = $this->tokens();
		$found = array();
		if (is_array($range)) {
			$start = 0;
			$end = count($range);
		} else {
			$start = $range;
			$end = count($tokens);
		}
		for ($idx = $start;$idx < $end;$idx++) {
			$id = is_array($range) ? $range[$idx] : $idx;
			$token = isset($tokens[$id]);
			if ($tokens && in_array($tokens[$id]['content'], $types)) {
				$found[] = $id;
			}
		}
		return $found;
	}

	/**
	 * A helper method which helps finding tokens. If there are no tokens
	 * on this line, we go backwards assuming a multiline token.
	 *
	 * @param  int    $line   The line you are on
	 * @return int            The token id if found, -1 if not
	 */
	public function findTokenByLine($line) {
		$lineCache = $this->lineCache();
		for (;$line >= 0;$line--) {
			if (isset($lineCache[$line])) {
				return $lineCache[$line][0];
			}
		}
		return -1;
	}

	/**
	 * Will determine if a set of tokens is on a given line.
	 *
	 * @param  int    $line     The line you are on
	 * @param  array  $tokenIds The tokens you are looking for
	 * @return int              The token id if found, -1 if not
	 */
	public function lineHasToken($line, array $tokenIds = array()) {
		$lineCache = $this->lineCache();
		if (!isset($lineCache[$line])) {
			return false;
		}
		$tokens = $this->tokens();
		foreach ($lineCache[$line] as $tokenId) {
			if (in_array($tokens[$tokenId]['id'], $tokenIds)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Will determine if the $needle has a parent of $haystack types
	 *
	 * @param  array $haystack Array of tokens
	 * @param  int   $needle   The index of token to analyze
	 * @return bool
	 */
	public function tokenIn(array $haystack, $needle) {
		$tokens = $this->tokens();
		$parent = $tokens[$needle]['parent'];
		while (isset($tokens[$parent])) {
			if (in_array($tokens[$parent]['id'], $haystack)) {
				return true;
			}
			$parent = $tokens[$parent]['parent'];
		}
		return false;
	}

	/**
	 * Accessor method for source lines.
	 *
	 * It returns each line of the source file in an array.
	 *
	 * @return  array
	 */
	public function lines($line = null) {
		if ($this->_lines === null) {
			$this->_lines = preg_split('/\r\n|\r|\n/', $this->source());
		}
		if ($line) {
			return $this->_lines[++$line];
		}
		return $this->_lines;
	}

}

?>