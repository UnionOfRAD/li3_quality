<?php

namespace li3_quality\tests\cases\analysis;

use li3_quality\analysis\Parser;

class ParserTest extends \li3_quality\test\Unit {

	public function testTokenCount() {
		$code = <<<EOD
class Foobar {
	public static function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$this->assertIdentical(26, count($tokens));
	}

	public function testParentAfterAbstractMethod() {
		$code = <<<EOD
class Foobar {
	abstract function foo(array \$bar);
	function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$bar = $tokens[18];
		$this->assertIdentical(T_FUNCTION, $bar['id']);

		$parent = $tokens[$bar['parent']];
		$this->assertIdentical(T_CLASS, $parent['id']);
	}

	public function testMethodName() {
		$code = <<<EOD
class Foobar {
	public static function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$function = $tokens[10];
		$this->assertIdentical(T_FUNCTION, $function['id']);
		$this->assertIdentical('bar', $function['label']);
	}

	public function testFunctionName() {
		$code = <<<EOD
function foobar() {
	return false;
}
EOD;
		$tokens = Parser::tokenize($code);
		$function = $tokens[0];
		$this->assertIdentical(T_FUNCTION, $function['id']);
		$this->assertIdentical('foobar', $function['label']);
	}

	public function testClassName() {
		$code = <<<EOD
class foobarbaz {
	protected \$foo = 'bar';
}
EOD;
		$tokens = Parser::tokenize($code);
		$class = $tokens[0];
		$this->assertIdentical(T_CLASS, $class['id']);
		$this->assertIdentical('foobarbaz', $class['label']);
	}

	public function testVariableName() {
		$code = <<<EOD
\$foo = 'bar';
EOD;
		$tokens = Parser::tokenize($code);
		$variable = $tokens[0];
		$this->assertIdentical(T_VARIABLE, $variable['id']);
		$this->assertIdentical('foo', $variable['label']);
	}

	public function testClassVariableName() {
		$code = <<<EOD
class baz {
	static \$foobar = 'bar';
}
EOD;
		$tokens = Parser::tokenize($code);
		$variable = $tokens[8];
		$this->assertIdentical(T_VARIABLE, $variable['id']);
		$this->assertIdentical('foobar', $variable['label']);
	}

	public function testBasicChildren() {
		$code = <<<EOD
class Foobar {
	public static \$foobar = 'bar';
	abstract function foo(array \$bar);
	public static function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$class = $tokens[0];
		$this->assertIdentical(T_CLASS, $class['id']);

		$this->assertIdentical(T_VARIABLE, $tokens[10]['id']);
		$this->assertTrue(in_array(10, $class['children']));

		$this->assertIdentical(T_FUNCTION, $tokens[19]['id']);
		$this->assertTrue(in_array(19, $class['children']));

		$this->assertIdentical(T_FUNCTION, $tokens[33]['id']);
		$this->assertTrue(in_array(33, $class['children']));
	}

	public function testMultiLineProperty() {
		$code = <<<EOD
class Foobar {
	public \$foo = array(
		array(
			true,
			false,
			true,
		),
		array(
			array(
				true,
				false,
				T_CLASS,
				array(
					T_FUNCTION,
					false,
					'foobar',
				),
			),
		),
	);
	abstract function foo(array \$bar);
	public static function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$class = $tokens[0];
		$this->assertIdentical(T_CLASS, $class['id']);

		$this->assertIdentical(T_VARIABLE, $tokens[8]['id']);
		$this->assertTrue(in_array(8, $class['children']));

		$this->assertIdentical(T_FUNCTION, $tokens[71]['id']);
		$this->assertTrue(in_array(71, $class['children']));

		$this->assertIdentical(T_FUNCTION, $tokens[85]['id']);
		$this->assertTrue(in_array(85, $class['children']));
	}

	public function testBracketsHaveCorrectParents() {
		$code = <<<EOD
class Foobar {
	public static \$foobar = 'bar';
	abstract function foo(array \$bar);
	public static function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$class = $tokens[0];
		$this->assertIdentical(T_CLASS, $class['id']);
		$this->assertIdentical('{', $tokens[4]['content']);
		$this->assertIdentical(0, $tokens[4]['parent']);
		$this->assertIdentical('}', $tokens[48]['content']);
		$this->assertIdentical(0, $tokens[48]['parent']);

		$bar = $tokens[33];
		$this->assertIdentical(T_FUNCTION, $bar['id']);
		$this->assertIdentical('{', $tokens[39]['content']);
		$this->assertIdentical(33, $tokens[39]['parent']);
		$this->assertIdentical('}', $tokens[46]['content']);
		$this->assertIdentical(33, $tokens[46]['parent']);
	}

	public function testRelationshipsLinkCorrectly() {
		$code = <<<EOD
class Foobar {
	public \$foo = array(
		array(
			true,
			false,
			true,
		),
		array(
			array(
				true,
				false,
				T_CLASS,
				array(
					T_FUNCTION,
					false,
					'foobar',
				),
			),
		),
	);
	abstract function foo(array \$bar);
	public static function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$failure = false;
		foreach ($tokens as $tokenId => $token) {
			if ($token['parent'] !== -1) {
				$parent = $tokens[$token['parent']];
				if (!in_array($tokenId, $parent['children'])) {
					$this->assert(false, "Token {$tokenId} not in parent");
					$failure = true;
				}
			}
		}
		if (!$failure) {
			$this->assert(true);
		}
	}

	public function testSimpleStructures() {
		$code = <<<EOD
while (true) {
	false;
}
if (true) {
	false;
}
foreach (true) {
	false;
}
do {
	false;
} while(true);
EOD;

		$tokens = Parser::tokenize($code);

		$while = $tokens[0];
		$this->assertIdentical(T_WHILE, $while['id']);
		$this->assertIdentical(-1, $while['parent']);

		$if = $tokens[13];
		$this->assertIdentical(T_IF, $if['id']);
		$this->assertIdentical(-1, $if['parent']);

		$foreach = $tokens[26];
		$this->assertIdentical(T_FOREACH, $foreach['id']);
		$this->assertIdentical(-1, $foreach['parent']);

		$do = $tokens[39];
		$this->assertIdentical(T_DO, $do['id']);
		$this->assertIdentical(-1, $if['parent']);

		$dowhile = $tokens[48];
		$this->assertIdentical(T_WHILE, $dowhile['id']);
		$this->assertIdentical(39, $dowhile['parent']);
	}

	public function testDoWhileParent() {
		$code = <<<EOD
\$i = 0;
do {
	\$i++;
} while(false);
EOD;
		$tokens = Parser::tokenize($code);
		$while = $tokens[17];
		$this->assertIdentical(T_WHILE, $while['id']);
		$this->assertIdentical(T_DO, $tokens[$while['parent']]['id']);
	}

	public function testComplexDoWhileParent() {
		$code = <<<EOD
do {
	do {
		true;
	} while(false);
} while(false);
EOD;
		$tokens = Parser::tokenize($code);

		// Inner do/while
		$while = $tokens[13];
		$this->assertIdentical(T_WHILE, $while['id']);
		$this->assertIdentical(4, $while['parent']);
		$this->assertIdentical(T_DO, $tokens[$while['parent']]['id']);

		// Outter do/while
		$while = $tokens[21];
		$this->assertIdentical(T_WHILE, $while['id']);
		$this->assertIdentical(0, $while['parent']);
		$this->assertIdentical(T_DO, $tokens[$while['parent']]['id']);
	}

	public function testNoParents() {
		$code = <<<EOD
return (!empty(\$name)) ? "{\$path}/{\$name}" : \$path;
EOD;
		$tokens = Parser::tokenize($code);

		foreach ($tokens as $token) {
			$this->assertIdentical(-1, $token['parent']);
		}
	}

	public function testDontQueueStaticCall() {
		$code = <<<EOD
class Rules {
	function get() {
		return static::\$_rules;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$this->assertIdentical(6, $tokens[16]['parent']);
	}

	public function testParseErrorTokenCount() {
		$code = <<<EOD
if (true) {
	false;
} else if (true) {
	false;
} elseif (true) {
	false
}
EOD;
		$tokens = Parser::tokenize($code);
		$this->assertIdentical(39, count($tokens));
	}

	public function testDocBlockWithClassParent() {
		$code = <<<EOD
/**
 * Do I have a parent?
 */
class Rules {}
EOD;
		$tokens = Parser::tokenize($code);

		$this->assertIdentical(T_DOC_COMMENT, $tokens[0]['id']);
		$this->assertIdentical(T_CLASS, $tokens[2]['id']);

		$expected = 2;
		$result = $tokens[0]['parent'];
		$this->assertIdentical($expected, $result);
	}

	public function testDocBlockTooFarHasNoParent() {
		$code = <<<EOD
/**
 * Do I have a parent?
 */

class Rules {}
EOD;
		$tokens = Parser::tokenize($code);

		$this->assertIdentical(T_DOC_COMMENT, $tokens[0]['id']);
		$this->assertIdentical(T_CLASS, $tokens[2]['id']);

		$expected = -1;
		$result = $tokens[0]['parent'];
		$this->assertIdentical($expected, $result);
	}

	public function testDocBlockHasMethodParent() {
		$code = <<<EOD
class Foobar {

	/**
	 * This is a docblock
	 */
	public static function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$docblock = $tokens[6];
		$this->assertIdentical(T_DOC_COMMENT, $docblock['id']);

		$parent = $tokens[$docblock['parent']];
		$this->assertIdentical(T_FUNCTION, $parent['id']);
	}

	public function testDocBlockHasClassParent() {
		$code = <<<EOD
class Foobar {

	/**
	 * This is a docblock
	 */

	public static function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$docblock = $tokens[6];
		$this->assertIdentical(T_DOC_COMMENT, $docblock['id']);

		$parent = $tokens[$docblock['parent']];
		$this->assertIdentical(T_CLASS, $parent['id']);
	}

	public function testDocBlockHasVarParent() {
		$code = <<<EOD
/**
 * The Quality command helps you to run static code analysis on your codebase.
 */
class Quality {

	/**
	 * The library to run the quality checks on.
	 */
	public \$library = true;
}
EOD;
		$tokens = Parser::tokenize($code);
		$docblock = $tokens[8];
		$this->assertIdentical(T_DOC_COMMENT, $docblock['id']);

		$parent = $tokens[$docblock['parent']];
		$this->assertIdentical(T_VARIABLE, $parent['id']);
	}

	public function testLevelStarts() {
		$code = <<<EOD
class Foobar {
	abstract function foo(array \$bar);
	public function bar() {
		return false;
	}
}
EOD;
		$tokens = Parser::tokenize($code);

		$abstract = $tokens[6];
		$this->assertIdentical(T_ABSTRACT, $abstract['id']);
		$this->assertIdentical(1, $abstract['level']);

		$foo = $tokens[10];
		$this->assertIdentical(T_STRING, $foo['id']);
		$this->assertIdentical(2, $foo['level']);

		$public = $tokens[18];
		$this->assertIdentical(T_PUBLIC, $public['id']);
		$this->assertIdentical(1, $public['level']);

		$bar = $tokens[22];
		$this->assertIdentical(T_STRING, $bar['id']);
		$this->assertIdentical(2, $bar['level']);
	}

	public function testIncompleteArrayException() {
		$this->assertException('LogicException', function() {
			$code = <<<EOD
class Foobar {
	\$foo = array(
		'bar',
}
EOD;
			$tokens = Parser::tokenize($code);
		});
	}

	public function testIncompleteDoWhile() {
		$this->assertException('LogicException', function() {
			$code = <<<EOD
do {

} while()
EOD;
			$tokens = Parser::tokenize($code);
		});
	}

}

?>