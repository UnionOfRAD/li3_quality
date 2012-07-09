<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

use SplFixedArray;
use lithium\core\Libraries;
use lithium\analysis\Parser;

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
		$this->_config = $config;
		$path = Libraries::path($config['path']);
		$this->_config['path'] = $path;
		$this->_source = file_get_contents($path);
	}

	/**
	 * Config accessor method.
	 */
	public function config($param = null) {
		if ($param === null) {
			return $this->_config;
		}
		return isset($this->_config[$param]) ? $this->_config[$param] : null;
	}

	/**
	 * Accessor method for the source code.
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
	 */
	public function tokens() {
		if($this->_tokens === null) {
			$this->_tokens = SplFixedArray::fromArray(Parser::tokenize($this->source()));
		}
		return $this->_tokens;
	}

	/**
	 * Accessor method for source lines.
	 *
	 * It returns each line of the source file in an array.
	 */
	public function lines($line = null) {
		$lineEnding = function($source) {
			if(strpos($source, "\r\n") !== false) {
				return "\r\n";
			} elseif(strpos($source, "\r") !== false) {
				return "\r";
			} else {
				return "\n";
			}
		};

		if($this->_lines === null) {
			$this->_lines = explode($lineEnding($this->source()), $this->source());
		}
		if($line) {
			return $this->_lines[++$line];
		}
		return $this->_lines;
	}

}

?>