<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\extensions\command;

use li3_quality\analysis\ParserException;

/**
 * The Quality command helps you to run static code analysis on your codebase.
 */
class Quality extends \lithium\console\command\Test {

	/**
	 * The library to run the quality checks on.
	 */
	public $library = true;

	/**
	 * If `--silent NUM` is used, only classes below this coverage are shown.
	 */
	public $threshold = 100;

	/**
	 * A regular expression to filter testable files.
	 */
	public $exclude = 'resources|webroot|vendor|libraries';

	/**
	 * This is the minimum threshold for core tests to be green.
	 */
	protected $_greenThreshold = 85;

	/**
	 * Dynamic dependencies.
	 *
	 * @var array
	 */
	protected $_classes = array(
		'response' => 'lithium\console\Response',
		'libraries' => 'lithium\core\Libraries',
		'dispatcher' => 'lithium\test\Dispatcher',
		'group' => 'lithium\test\Group',
		'rules' => 'li3_quality\test\Rules',
		'testable' => 'li3_quality\test\Testable'
	);

	/**
	 * Show help on run.
	 *
	 */
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
		$rules = $this->_classes['rules'];
		$ruleOptions = array();
		$testables = $this->_testables(compact('path'));
		$this->header('Lithium Syntax Check');

		$filters = $this->_syntaxFilters();
		$ruleCount = count($rules::filterByName($filters));
		$classCount = count($testables);
		$this->out("Performing {$ruleCount} rules on {$classCount} classes.");
		$success = true;
		foreach ($testables as $count => $path) {
			try {
				$testable = $this->_instance('testable', compact('path'));
				$result = $rules::apply($testable, $filters, $ruleOptions);
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
				$this->out("[OK  ] $path", "green");
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
	 * Checks for undocumented classes or methods inside the library.
	 */
	public function documented() {
		$this->header('Lithium Documentation Check');

		$testables = $this->_testables();
		$count = count($testables);
		$this->out("Checking documentation on {$count} classes.");

		foreach ($testables as $count => $path) {
		}
	}

	/**
	 * Lists code coverage for a given threshold (100 by default).
	 */
	public function coverage() {
		$this->header('Lithium Code Coverage');

		$exclude = 'tests|index$|^app\\\\config|^app\\\\views|Exception$';
		$testables = $this->_testables(compact('exclude'));

		$this->out("Checking coverage on " . count($testables) . " classes.");

		$tests = array();
		$group = $this->_classes['group'];
		foreach ($group::all() as $test) {
			$class = preg_replace('/(tests\\\[a-z]+\\\|Test$)/', null, $test);
			$tests[$class] = $test;
		}

		$dispatcher = $this->_classes['dispatcher'];
		foreach ($testables as $count => $path) {
			$coverage = null;

			if ($hasTest = isset($tests[$path])) {
				$report = $dispatcher::run($tests[$path], array(
					'format' => 'txt',
					'filters' => array('Coverage')
				));
				$filter = 'lithium\test\filter\Coverage';
				$collected = $report->results['filters'][$filter];
				if (isset($collected[$path])) {
					$coverage = $collected[$path]['percentage'];
				}
			}

			if ($coverage >= $this->_greenThreshold) {
				$color = 'green';
			} elseif ($coverage === null || $coverage === 0) {
				$color = 'red';
			} else {
				$color = 'yellow';
			}

			if ($coverage === null || $coverage <= $this->threshold) {
				$label = $hasTest ? 'has test' : 'no test';
				$cov = 'n/a';
				if (is_numeric($coverage)) {
					$cov = sprintf('%.2f%%', $coverage);
				}
				$output = sprintf('%10s | %7s | %s', $label, $cov, $path);
				$this->out($output, $color);
			}
		}

	}

	/**
	 * Returns a list of testable classes according to the given library.
	 */
	protected function _testables($options = array()) {
		$defaults = array(
			'recursive' => true, 'path' => null, 'exclude' => null
		);
		$options += $defaults;

		$exclude = array($this->exclude, $options['exclude']);
		if ($exclude = array_filter($exclude)) {
			$options['exclude'] = '/' . join('|', $exclude) . '/';
		} else {
			unset($options['exclude']);
		}

		if ($path = $this->_path($options['path'])) {
			if (pathinfo($options['path'], PATHINFO_EXTENSION) === 'php') {
				return array($path);
			}
			$parts = explode('\\', $path) + array($this->library);
			$this->library = array_shift($parts);
			$options['path'] = join('/', $parts);
		}
		$libraries = $this->_classes['libraries'];
		$testables = $libraries::find($this->library, $options);

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
			$filters = array();
			if ($this->filters) {
				$filters = array_map('trim', explode(',', $this->filters));
			}
			if (count($filters) === 0) {
				$libraries = $this->_classes['libraries'];
				$config = $libraries::get($this->library);
				$ruleConfig = $config['path'] . '/test/rules.json';
				if (!file_exists($ruleConfig)) {
					$config = $libraries::get('li3_quality');
					$ruleConfig = $config['path'] . '/test/defaultRules.json';
				}
				$ruleConfig = json_decode(file_get_contents($ruleConfig), true);
				$filters = $ruleConfig['rules'];
				if (isset($ruleConfig['variables'])) {
					$rules = $this->_classes['rules'];
					$rules::ruleOptions($ruleConfig['variables']);
				}
			}
			$this->filters = $filters;
		}
		return $this->filters;
	}

}

?>