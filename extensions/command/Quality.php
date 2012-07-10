<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\extensions\command;

use lithium\core\Libraries;
use lithium\analysis\Inspector;
use lithium\test\Dispatcher;
use lithium\test\Group;
use li3_quality\test\Rules;
use li3_quality\test\Testable;

/**
 * The Quality command helps you to run static code analysis on your codebase.
 */
class Quality extends \lithium\console\command\Test {

	/**
	 * The library to run the quality checks on.
	 */
	public $library = "app";

	/**
	 * If `--silent` is used, only failures are shown.
	 */
	public $silent = false;

	/**
	 * Color the output.
	 */
	public $colorize = false;

	/**
	 * If `--slient NUM` is used, only classes below this coverage are shown.
	 */
	public $threshold = 100;

	/**
	 * This is the minimum threshold for core tests to be green.
	 */
	protected $_greenThreshold = 85;


	protected function _init() {
		parent::_init();

		if (!$this->colorize) {
			$this->response->styles(false);
		}
	}

	public function run($path = null) {
		return $this->_help();
	}

	/**
	 * Checks the syntax of your class files through static code analysis.
	 * if GIT_DIR env variable is set, then use plain and silent.
	 */
	public function syntax($path = null) {
		if ($this->request->env('GIT_DIR')) {
			$this->plain = true;
			$this->silent = true;
		}
		$pass = true;
		$testables = $this->_testables(compact('path'));
		$this->header('Lithium Syntax Check');
		$this->out(
			"Performing ". count(Rules::get()) . " rules"
			. "on ". count($testables) . " classes."
		);

		foreach($testables as $count => $path) {
			$result = Rules::apply(new Testable(compact('path')));
			if($result['success']) {
				$this->out("[OK] $path", "green");
			}
			if(!$result['success']) {
				$pass = false;
				$this->error("[FAIL] $path", "red");
				$output = array(
					array("Line", "Position", "Violation"),
					array("----", "--------", "---------")
				);
				foreach($result['violations'] as $violation) {
					$defaults = array(
						'line' => '-',
						'position' => '-',
						'message' => 'Unnamed Violation'
					);
					$params = $violation + $defaults;
					extract($params);
					$output[] = array($line, $position, $message);
				}
				$this->columns($output, array('style' => 'red', 'error' => true));
			}
		}
		return (boolean) !$pass;
	}

	/**
	 * Checks for undocumented classes or methods inside the library.
	 */
	public function documented() {
		$this->header('Lithium Documentation Check');

		$testables = $this->_testables();

		$this->out("Checking documentation on " . count($testables) . " classes.");

		foreach($testables as $count => $path) {
			//
		}
	}

	/**
	 * Lists code coverage for a given threshold (100 by default).
	 */
	public function coverage() {
		$this->header('Lithium Code Coverage');

		$testables = $this->_testables(array(
			'exclude' => '/tests|resources|webroot|index$|^app\\\\config|^app\\\\views|Exception$/'
		));

		$this->out("Checking coverage on " . count($testables) . " classes.");

		$tests = array();
		foreach (Group::all() as $test) {
			$class = preg_replace('/(tests\\\[a-z]+\\\|Test$)/', null, $test);
			$tests[$class] = $test;
		}

		foreach($testables as $count => $path) {
			$coverage = null;

			if($hasTest = isset($tests[$path])) {
				$report = Dispatcher::run($tests[$path], array(
					'format' => 'txt',
					'filters' => array('Coverage')
				));
				$coverage = $report->results['filters']['lithium\test\filter\Coverage'];
				$coverage = isset($coverage[$path]) ? $coverage[$path]['percentage'] : null;
			}

			if ($coverage >= $this->_greenThreshold) {
				$color = 'green';
			} elseif ($coverage === null || $coverage === 0) {
				$color = 'red';
			} else {
				$color = 'yellow';
			}

			if($coverage == null || $coverage <= $this->threshold) {
				$this->out(sprintf('%10s | %7s | %s',
					$hasTest ? 'has test' : 'no test',
					is_numeric($coverage) ? sprintf('%.2f%%', $coverage) : 'n/a',
					$path
				), $color);
			}
		}

	}

	/**
	 * Returns a list of testable classes according to the given library.
	 */
	protected function _testables($options = array()) {
		$defaults = array('recursive' => true, 'path' => null);
		$options += $defaults;

		if ($path = $this->_path($options['path'])) {
			if (pathinfo($options['path'], PATHINFO_EXTENSION) == 'php') {
				return array($path);
			}
			$parts = explode('\\', $path) + array($this->library);
			$this->library = array_shift($parts);
			$options['path'] = '/' . join('/', $parts);
		}
		$testables = Libraries::find($this->library, $options);

		if(!$testables) {
			$library = $path ? $path : $this->library;
			$this->stop(0, "Could not find any files in {$library}.");
		}
		return $testables;
	}
}

?>