<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\extensions\command;

use SplFileInfo;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use lithium\core\Libraries;
use li3_quality\qa\Rules;
use li3_quality\analysis\ParserException;
use li3_quality\qa\Testable;

/**
 * The Syntax command helps you to run static code analysis on your codebase and
 * detect common coding standard violations.
 *
 * Supports multiple paths/files and already expanded wildcards
 * passed through the shell.
 * ```
 * li3 syntax app/controllers/HelloWorldController.php
 * li3 syntax app/controllers
 * li3 syntax app/{controllers,models}
 * li3 syntax app/controllers app/models
 * ```
 *
 * You should use dedicated syntax rule sets for class files, templates and
 * procedural files.
 * ```
 * li3 syntax --config=classSyntax.json app/controllers/HelloWorldController.php
 * li3 syntax --config=viewSyntax.json app/views
 * li3 syntax --config=configSyntax.json app/config
 * ```
 */
class Syntax extends \lithium\console\Command {

	/**
	 * Path to syntax rules configuration file.
	 *
	 * @var string
	 */
	public $config;

	/**
	 * Enable verbose output.
	 *
	 * @var boolean
	 */
	public $verbose = false;

	/**
	 * Runs the syntax checker on given path/s.
	 *
	 * @param string $path Absolute or relative path to a directory
	 *        to search recursively for files to check. By default will not descend
	 *        into known library directories (i.e. `libraries`, `vendors`). If ommitted
	 *        will use current working directory. Also works on single files.
	 * @return boolean Will (indirectly) exit with status `1` if one or more rules
	 *         failed otherwise with `0`.
	 */
	public function run($path = null /* , ... */) {
		$this->header('Syntax Check');

		$rules = $this->_rules();
		$subjects = array();
		$success = true;

		foreach (func_get_args() ?: array(getcwd()) as $path) {
			$subjects = array_merge($subjects, $this->_subjects($path));
		}

		$this->out(sprintf(
			'Performing %d rules on path `%s`...',
			count($rules),
			$path
		));

		foreach ($subjects as $path) {
			$testable = new Testable(array('path' => $path->getPathname()));

			try {
				$result = Rules::apply($testable, $rules);
			} catch (ParserException $e) {
				$this->error("[FAIL] $path", "red");
				$this->error("Parse error: " . $e->getMessage(), "red");

				if ($this->verbose) {
					$this->error(print_r($e->parserData, true), "red");
				}

				$success = false;
				continue;
			}
			if ($result['success']) {
				if ($this->verbose) {
					$this->out("[OK  ] $path", "green");
				}
			} else {
				$this->error("[FAIL] $path", "red");
				$output = array(
					array("Line", "Position", "Violation"),
					array("----", "--------", "---------")
				);
				foreach ($result['violations'] as $violation) {
					$params = $violation;
					$output[] = array(
						$params['line'],
						$params['position'],
						$params['message']
					);
				}
				$this->columns($output, array(
					'style' => 'red', 'error' => true
				));
				$success = false;
			}
			if (count($result['warnings']) > 0) {
				$output = array(
					array("Line", "Position", "Warning"),
					array("----", "--------", "-------")
				);
				foreach ($result['warnings'] as $warning) {
					$params = $warning;
					$output[] = array(
						$params['line'],
						$params['position'],
						$params['message']
					);
				}
				$this->columns($output, array(
					'style' => 'yellow', 'error' => false
				));
			}
		}
		return $success;
	}

	/**
	 * Retrieves subject to test. Will return only PHP files.
	 *
	 * @param string $path
	 * @return array Returns an array of SplFieldInfo objects.
	 */
	protected function _subjects($path) {
		if (is_file($path)) {
			$current = new SplFileInfo($path);
			return $current->getExtension() === 'php' ? array($current) : array();
		}
		$files = new RecursiveCallbackFilterIterator(
			new RecursiveDirectoryIterator($path),
			function($current, $key, $iterator) {
				$noDescend = array(
					'.git',
					'libraries',
					'vendor'
				);
				if ($iterator->hasChildren()) {
					if ($current->isDir() && in_array($current->getBasename(), $noDescend)) {
						return false;
					}
					return true;
				}
				if ($current->isFile()) {
					return $current->getExtension() === 'php';
				}
				return false;
			}
		);
		return iterator_to_array(new RecursiveIteratorIterator($files));
	}

	protected function _rules() {
		$rules = array();

		$files = array(
			$this->config,
			Libraries::get('li3_quality', 'path') . '/config/syntax.json'
		);
		foreach ($files as $file) {
			if (file_exists($file)) {
				$this->out("Loading configuration file `{$file}`...");
				$config = json_decode(file_get_contents($file), true) + array(
					'rules' => array(),
					'variables' => array()
				);
				break;
			}
		}

		if ($config['variables']) {
			Rules::ruleOptions($config['variables']);
		}
		return $config['rules'];
	}
}

?>