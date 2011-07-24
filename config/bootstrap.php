<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use li3_quality\test\Rules;

/**
 * Rule 1: Don't allow a new line at the end of the file.
 */
Rules::add(function($self, $testable) {
	$message = "EOL at EOF";
	$lines = &$testable->lines();
	$lastLine = trim($lines[count($lines)-1]);
	
	if(empty($lastLine)) {
		$self->addViolation(array(
			'message' => $message,
			'line' => count($lines)
		));
	}
});

/**
 * Rule 2: Line length must not exceed 100 chars.
 */
Rules::add(function($self, $testable) {
	$message = "Maximum line lenth exceeded";
	$maxLength = 100;
	$tabWidth  = 3;
	
	foreach($testable->lines() as $i => $line) {
		$tabBounty = substr_count($line, "\t") * $tabWidth;
		if(($length = $tabBounty + strlen($line)) > 100) {
			$self->addViolation(array(
				'message' => $message,
				'line' => $i+1, 
				'position' => $length
			));
		}
	}
});

/**
 * Rule 3: Do not allow carriage return characters.
 */
Rules::add(function($self, $testable) {
	$message = "Carriage Return character found";
	
	foreach($testable->lines() as $i => $line) {
		if(($pos = strpos($line, "\r")) !== false) {
			$self->addViolation(array(
				'message' => $message,
				'line' => $i+1,
				'position' => $pos
			));
		}
	}
});

/**
 * Rule 4: Do not allow trailing whitespace.
 */
Rules::add(function($self, $testable) {
	$message = "Trailing whitespace found";
	
	foreach($testable->lines() as $i => $line) {
		$lineLength = strlen($line);
		if ($lineLength != strlen(rtrim($line))) {
			$self->addViolation(array(
				'message' => $message,
				'line' => $i+1,
				'position' => $lineLength
			));
		}
	}
});

/**
 * Rule 5: Class files are not allowed to be executable.
 */
Rules::add(function($self, $testable) {
	$message = "File is executable";
	if(is_executable($testable->config('path'))) {
			$self->addViolation(compact('message'));		
	}
});
?>