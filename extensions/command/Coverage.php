<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\extensions\command;

/**
 * The Quality command helps you to run static code analysis on your codebase.
 */
class Coverage extends \lithium\console\command\Test {

	/**
	 * The library to run the quality checks on.
	 */
	public $library = true;

	/**
	 * If `--slient NUM` is used, only classes below this coverage are shown.
	 */
	public $threshold = 100;

	/**
	 * A regular experssion to filter testable files.
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
		'libraries' => 'lithium\core\Libraries',
		'dispatcher' => 'lithium\test\Dispatcher',
		'group' => 'lithium\test\Group',
		'testable' => 'li3_quality\qa\Testable'
	);

	/**
	 * Lists code coverage for a given threshold (100 by default).
	 */
	public function run($path = null) {
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
}

?>