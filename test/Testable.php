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
	 */
	protected $_source = null;

	/**
	 * Contains the source code as an array for each line.
	 */
	protected $_lines = null;

	/**
	 * Contains the class tokens.
	 */
	protected $_tokens = null;

	/**
	 * Contains the config of the testable class.
	 */
	protected $_config = array();

	/**
	 * Locates the file and reads its source code.
	 */
	public function __construct($config = array()) {
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
			$this->_tokens = Parser::tokenize($this->source(), $this->_config);
		}
		return $this->_tokens;
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
		$total = count($tokens);
		if (!is_array($range)) {
			$range = range($range, $total);
		}
		foreach ($range as $id) {
			if (isset($tokens[$id]) && in_array($tokens[$id]['content'], $types)) {
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
		if (!is_array($range)) {
			$range = range($range, 0);
		} else {
			$range = array_reverse($range);
		}
		foreach ($range as $id) {
			if (isset($tokens[$id]) && in_array($tokens[$id]['content'], $types)) {
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
		$total = count($tokens);
		if (!is_array($range)) {
			$range = range($range, $total);
		} else {
			$range = array_reverse($range);
		}
		foreach ($range as $id) {
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
	 * @param  integer|array   $start Where you want to start, or an array of items to search
	 * @return integer|boolean        The index of the next $type or false if nothing is found
	 */
	public function findPrev(array $types, $range = 0) {
		$tokens = $this->tokens();
		if (!is_array($range)) {
			$range = range($range, 0);
		} else {
			$range = array_reverse($range);
		}
		foreach ($range as $id) {
			if (isset($tokens[$id]) && in_array($tokens[$id]['id'], $types)) {
				return $id;
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
		$self = $tokens[$needle];
		while (isset($tokens[$self['parent']])) {
			if (in_array($tokens[$self['parent']]['id'], $haystack)) {
				return true;
			}
			$self = $tokens[$self['parent']];
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