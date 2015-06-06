<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2015, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\extensions\command;

use SplFileInfo;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use lithium\core\Libraries;
use li3_quality\qa\Rules;
use li3_quality\qa\Testable;

/**
 * Applies fixes to files containing PHP source code.
 *
 * It is assumed that the code being transformed is under version control
 * thus *no backups* are made and their are no further safety mechanisms.
 *
 * This assumption heavily simplifies the implementation of the tool.
 */
class Fix extends \lithium\console\Command {

	/**
	 * Path to rules configuration file.
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
	 * Runs the fix tool on given path/s.
	 *
	 * @param string $path Absolute or relative path to a directory
	 *        to search recursively for files to check. By default will not descend
	 *        into known library directories (i.e. `libraries`, `vendors`). If ommitted
	 *        will use current working directory. Also works on single files.
	 * @return boolean Will (indirectly) exit with status `1` if one or more rules
	 *         failed otherwise with `0`.
	 */
	public function run($path = null /* , ... */) {
		$this->header('Fix');

		$message = array(
			'{:red}This command will modify files in given paths. These',
			'modifications are final and cannot be reversed. Your should',
			'use a VCS or backup the files before continuing.{:end}'
		);
		$this->out($message);
		$result = $this->in('Continue?', array(
			'choices' => array('y', 'n'),
			'default' => 'n'
		));
		if ($result !== 'y') {
			$this->error('Cancelled.');
			return false;
		}

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
			$result = $rules->apply($testable);

			if ($result['success']) {
				$this->out("[OK  ] $path", "green");
				$testable->save();
			}
		}
		return $success;
	}

	/**
	 * Retrieves subjects. Will return only PHP files.
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

	/**
	 * Loads rules configuration.
	 *
	 * @return object
	 */
	protected function _rules() {
		$rules = new Rules();

		$files = array(
			$this->config,
			Libraries::get('li3_quality', 'path') . '/config/fix.json'
		);
		foreach ($files as $file) {
			if (file_exists($file)) {
				$this->out("Loading configuration file `{$file}`...");
				$config = json_decode(file_get_contents($file), true) + array(
					'name' => null,
					'rules' => array(),
					'options' => array()
				);
				break;
			}
		}

		foreach ($config['rules'] as $ruleName) {
			$class = Libraries::locate('rules.fix', $ruleName);
			$rules->add(new $class());
		}
		if ($config['options']) {
			$rules->options($config['options']);
		}
		return $rules;
	}
}

?>