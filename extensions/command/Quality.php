<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\extensions\command;

use lithium\core\Libraries;
use lithium\test\Dispatcher;
use lithium\test\Group;
use li3_quality\test\Rules;
use li3_quality\test\Testable;
use li3_quality\analysis\ParserException;

/**
 * The Quality command helps you to run static code analysis on your codebase.
 */
class Quality extends \lithium\console\command\Test {

	/**
	 * The library to run the quality checks on.
	 *
	 * @var  int
	 */
	public $library = true;

	/**
	 * If `--slient NUM` is used, only classes below this coverage are shown.
	 *
	 * @var  int
	 */
	public $threshold = 100;

	/**
	 * This is the minimum threshold for core tests to be green.
	 *
	 * @var  int
	 */
	protected $_greenThreshold = 85;

	/**
	 * Show help on run.
	 *
	 * @return boolean
	 */
	public function run($path = null) {
		return $this->_help();
	}

	/**
	 * Checks the syntax of your class files through static code analysis.
	 * if GIT_DIR env variable is set, then use plain and silent.
	 *
	 * @return  void
	 */
	public function syntax($path = null) {
		if ($this->request->env('GIT_DIR')) {
			$this->plain = true;
			$this->silent = true;
		}
		$ruleOptions = array();
		$testables = $this->_testables(compact('path'));
		$this->header('Lithium Syntax Check');

		$filters = $this->_syntaxFilters();
		$ruleCount = count(Rules::filterByName($filters));
		$classCount = count($testables);
		$this->out("Performing {$ruleCount} rules on {$classCount} classes.");
		$success = true;
		foreach ($testables as $count => $path) {
			try {
				$result = Rules::apply(new Testable(compact('path')), $filters, $ruleOptions);
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
				$this->out("[OK] $path", "green");
			} else {
				$this->error("[FAIL] $path", "red");
				$output = array(
					array("Line", "Position", "Violation"),
					array("----", "--------", "---------")
				);
				foreach ($result['violations'] as $violation) {
					$params = $violation;
					$output[] = array($params['line'], $params['position'], $params['message']);
				}
				$this->columns($output, array('style' => 'red', 'error' => true));
				$success = false;
			}
			if (count($result['warnings']) > 0) {
				$output = array(
					array("Line", "Position", "Warning"),
					array("----", "--------", "-------")
				);
				foreach ($result['warnings'] as $warning) {
					$params = $warning;
					$output[] = array($params['line'], $params['position'], $params['message']);
				}
				$this->columns($output, array('style' => 'yellow', 'error' => false));
			}
		}
		return $success;
	}

	/**
	 * Checks for undocumented classes or methods inside the library.
	 *
	 * @return  void
	 */
	public function documented() {
		$this->header('Lithium Documentation Check');

		$testables = $this->_testables();

		$this->out("Checking documentation on " . count($testables) . " classes.");

		foreach ($testables as $count => $path) {
		}
	}

	/**
	 * Lists code coverage for a given threshold (100 by default).
	 *
	 * @return  void
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

		foreach ($testables as $count => $path) {
			$coverage = null;

			if ($hasTest = isset($tests[$path])) {
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

			if ($coverage === null || $coverage <= $this->threshold) {
				$this->out(sprintf(
					'%10s | %7s | %s',
					$hasTest ? 'has test' : 'no test',
					is_numeric($coverage) ? sprintf('%.2f%%', $coverage) : 'n/a',
					$path
				), $color);
			}
		}

	}

	/**
	 * Returns a list of testable classes according to the given library.
	 *
	 * @return  array
	 */
	protected function _testables($options = array()) {
		$defaults = array('recursive' => true, 'path' => null);
		$options += $defaults;

		if ($path = $this->_path($options['path'])) {
			if (pathinfo($options['path'], PATHINFO_EXTENSION) === 'php') {
				return array($path);
			}
			$parts = explode('\\', $path) + array($this->library);
			$this->library = array_shift($parts);
			$options['path'] = '/' . join('/', $parts);
		}
		$testables = Libraries::find($this->library, $options);

		if (!$testables) {
			$library = $path ? $path : $this->library;
			$this->stop(0, "Could not find any files in {$library}.");
		}
		return $testables;
	}

	/**
	 * Will get the filters either from the filter option or the json ruleset
	 *
	 * @return array
	 */
	protected function _syntaxFilters() {
		if (!is_array($this->filters)) {
			$filters = $this->filters ? array_map('trim', explode(',', $this->filters)) : array();
			if (count($filters) === 0) {
				list($ruleConfig) = Libraries::locate('ruleSets', null, array(
					'recursive' => false,
					'suffix' => '.json',
					'format' => false,
					'preFilter' => '/(r|defaultR)ules\.json/',
				));
				$ruleConfig = json_decode(file_get_contents($ruleConfig), true);
				$filters = $ruleConfig['rules'];
				if (isset($ruleConfig['variables'])) {
					Rules::ruleOptions($ruleConfig['variables']);
				}
			}
			$this->filters = $filters;
		}
		return $this->filters;
	}

	/**
	 * Will decode json string into an object
	 *
	 * @param  string $text
	 * @return array
	 */
	protected function _decode($text) {
		if (($data = json_decode($text, true)) === null) {
			throw new \Exception('JSON was not decoded correctly.');
		}
		return $data;
	}

}

?>