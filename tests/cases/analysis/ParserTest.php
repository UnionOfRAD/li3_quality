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
		$tokens = Parser::relationships($tokens);

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
		$tokens = Parser::relationships($tokens);

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
		$this->assertIdentical('bar', Parser::label(10, $tokens));
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
		$this->assertIdentical('foobar', Parser::label(0, $tokens));
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
		$this->assertIdentical('foobarbaz', Parser::label(0, $tokens));
	}

	public function testVariableName() {
		$code = <<<EOD
\$foo = 'bar';
EOD;
		$tokens = Parser::tokenize($code);

		$variable = $tokens[0];
		$this->assertIdentical(T_VARIABLE, $variable['id']);
		$this->assertIdentical('foo', Parser::label(0, $tokens));
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
		$this->assertIdentical('foobar', Parser::label(8, $tokens));
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
		$tokens = Parser::relationships($tokens);
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
		$tokens = Parser::relationships($tokens);
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
		$tokens = Parser::relationships($tokens);
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
		$tokens = Parser::relationships($tokens);
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
		$tokens = Parser::relationships($tokens);

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
		$this->assertIdentical(-1, $dowhile['parent']);
	}

	public function testNoParents() {
		$code = <<<EOD
return (!empty(\$name)) ? "{\$path}/{\$name}" : \$path;
EOD;
		$tokens = Parser::tokenize($code);
		$tokens = Parser::relationships($tokens);

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
		$tokens = Parser::relationships($tokens);
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
		$tokens = Parser::relationships($tokens);
		$this->assertIdentical(39, count($tokens));
	}
	public function testDocBlockTooFarHasNoParent() {
		$code = <<<EOD
/**
 * Do I have a parent?
 */

class Rules {}
EOD;
		$tokens = Parser::tokenize($code);
		$tokens = Parser::relationships($tokens);

		$this->assertIdentical(T_DOC_COMMENT, $tokens[0]['id']);
		$this->assertIdentical(T_CLASS, $tokens[2]['id']);

		$expected = -1;
		$result = $tokens[0]['parent'];
		$this->assertIdentical($expected, $result);
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
		$tokens = Parser::relationships($tokens);
		$docblock = $tokens[6];
		$this->assertIdentical(T_DOC_COMMENT, $docblock['id']);

		$parent = $tokens[$docblock['parent']];
		$this->assertIdentical(T_CLASS, $parent['id']);
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
		$tokens = Parser::relationships($tokens);

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
			$tokens = Parser::relationships($tokens);
		});
	}

	public function testIncompleteDoWhile() {
		$this->assertException('LogicException', function() {
			$code = <<<EOD
do {

} while()
EOD;
			$tokens = Parser::tokenize($code);
			$tokens = Parser::relationships($tokens);
		});
	}

	public function testAnonymousFunction() {
		$code = <<<EOD
return function() {
	return Parser::tokenize();
};
EOD;
		$tokens = Parser::tokenize($code);

		$function = $tokens[2];
		$this->assertIdentical(T_FUNCTION, $function['id']);
		$this->assertIdentical(null, Parser::label(2, $tokens));
	}

	public function testAnonymousClass() {
		$code = <<<EOD
class {
	return Parser::tokenize();
};
EOD;
		$tokens = Parser::tokenize($code);

		$class = $tokens[0];
		$this->assertIdentical(T_CLASS, $class['id']);
		$this->assertIdentical(null, Parser::label(0, $tokens));
	}

	public function testComplexVariables() {
		$code = <<<EOD
class Quality {
	public \$foo = true;
	public function __construct() {
		\$this->{'foo'} = 'bar'
		\$this->{\$this->{'foo'}} = 'baz';
		\$this->{'foobar' . \$this->{'foo'}} = 'foobaz';
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$tokens = Parser::relationships($tokens);
		$this->assertIdentical(72, count($tokens));
	}

	public function testStaticDynamicVariable() {
		$code = <<<EOD
class Inflector {
	function rules() {
		static::\${\$var} = null;
	}
}
EOD;
		$tokens = Parser::tokenize($code);
		$tokens = Parser::relationships($tokens);
		$this->assertIdentical(29, count($tokens));
	}

	public function testModifiers() {
		$code = <<<EOD
class Inflector {
	public static function rules() {
		static::\${\$var} = null;
	}
}
EOD;
		$tokens = Parser::tokenize($code);

		$modifiers = Parser::modifiers(10, $tokens);
		$this->assertIdentical(array(8, 6), $modifiers);

		$public = $tokens[$modifiers[1]];
		$this->assertIdentical(T_PUBLIC, $public['id']);

		$static = $tokens[$modifiers[0]];
		$this->assertIdentical(T_STATIC, $static['id']);
	}

	public function testNoModifiers() {
		$code = <<<EOD
class Inflector {
	public static \$foo;
	function rules() {
		static::\${\$var} = null;
	}
}
EOD;
		$tokens = Parser::tokenize($code);

		$modifiers = Parser::modifiers(13, $tokens);
		$this->assertIdentical(T_FUNCTION, $tokens[13]['id']);
		$this->assertIdentical(array(), $modifiers);
	}

	public function testNonClosureIsClosure() {
		return;
		$code = <<<EOD
class Inflector {
	public static function rules() {
		static::\${\$var} = null;
	}
}
EOD;
		$tokens = Parser::tokenize($code);

		$this->assertIdentical(T_FUNCTION, $tokens[10]['id']);
		$isClosure = Parser::closure(10, $tokens);
		$this->assertFalse($isClosure);
	}

	public function testClosureIsClosure() {
		$code = <<<EOD
\$foo = function() {
	return false;
}
EOD;
		$tokens = Parser::tokenize($code);

		$this->assertIdentical(T_FUNCTION, $tokens[4]['id']);
		$isClosure = Parser::closure(4, $tokens);
		$this->assertTrue($isClosure);
	}

	public function testBasicParams() {
		$code = <<<EOD
function foo(\$bar, \$baz = null) {
	return false;
}
EOD;
		$tokens = Parser::tokenize($code);

		$this->assertIdentical(T_FUNCTION, $tokens[0]['id']);

		$params = Parser::parameters(0, $tokens);
		$this->assertIdentical(array(4, 7), $params);

		$this->assertIdentical('$bar', $tokens[$params[0]]['content']);
		$this->assertIdentical('$baz', $tokens[$params[1]]['content']);
	}

	public function testNoParams() {
		$code = <<<EOD
function foo() {
	return false;
}
EOD;
		$tokens = Parser::tokenize($code);

		$this->assertIdentical(T_FUNCTION, $tokens[0]['id']);

		$params = Parser::parameters(0, $tokens);
		$this->assertIdentical(array(), $params);
	}

}

?>