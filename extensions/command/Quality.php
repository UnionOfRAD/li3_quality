<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\extensions\command;

use lithium\core\Libraries;
use li3_quality\test\Rules;
use li3_quality\test\Testable;

/**
 * The Quality command helps you to run static code analysis on your codebase.
 */
class Quality extends \lithium\console\Command {

	/**
	 * The namespace to run the quality checks on.
	 */
	public $namespace = "app";
	
	/**
	 * If `--silent` is used, only failures are shown.
	 */
	public $silent = false;

	/**
	 * Checks the syntax of your class files through static code analysis.
	 */
	public function syntax() {
		$this->header('Lithium Syntax Check');

		$testables = Libraries::find($this->namespace, array('recursive' => true));
		if(!$testables) {
			$this->stop(0, "Could not find any tests in \"$this->namespace\"");
		}

		$this->out("Performing ". count(Rules::get()) . 
				   " checks on ". count($testables) . " classes.");
		
		foreach($testables as $count => $path) {
			$result = Rules::apply(new Testable(compact('path')));
			if($result['success'] && !$this->silent) {
				$this->out("[OK] $path", "green");
			}
			if(!$result['success']) {
				$this->out("[FAIL] $path", "red");
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
				$this->columns($output, array('style' => 'red'));
			}
		}
	}
	
}

?>