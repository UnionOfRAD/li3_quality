<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa\rules\syntax;

class ClassesDoNotUseSuperglobalsDirectly extends \li3_quality\qa\Rule {

	const PATTERN = '/^(\$GLOBALS|\$_SERVER|\$_REQUEST|\$_POST|\$_GET|\$_FILES|\$_ENV|\$_COOKIE|\$_SESSION)$/';

	/**
	 * The regexes to use on detecting docblocks
	 *
	 * @var array
	 */
	public $patterns = array(
		'SERVER'  => '/_SERVER/',
		'REQUEST' => '/_REQUEST/',
		'GET'     => '/_GET/',
		'POST'    => '/_POST/',
	);

	/**
	 * Will iterate tokens looking for comments and if found will determine the regex
	 * to test the comment against.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable, array $config = array()) {
		$tokens = $testable->tokens();
		$filtered = $testable->findAll(array(T_VARIABLE));

		$message = 'Superglobal usage detected in class';
		$currLine = 1;
		foreach ($filtered as $key) {
			$token = $tokens[$key];
			if (preg_match(static::PATTERN, $token['content'])) {
				$isNewLine = ($token['line'] > $currLine);
				$currLine = $token['line'];
				if (!$isNewLine) {
					continue;
				}

				$this->addViolation(array(
					'message' => $message,
					'line' => $token['line']
				));
			}
		}
	}

}

?>