<?php

namespace li3_quality\analysis;

/**
 * The `ParserException` is thrown when an error occurs in parsing a PHP script.
 *
 * @see li3_quality\analysis\Parser::tokenize()
 */
class ParserException extends \LogicException {
	public $parserData = array();
}

?>