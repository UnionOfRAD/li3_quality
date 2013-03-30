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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$bar = $tokens[18];
		$this->assertIdentical(T_FUNCTION, $bar['id']);

		$parent = $tokens[$tokens[18]['parent']];
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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$class = $tokens[0];
		$this->assertIdentical(T_CLASS, $class['id']);
		$this->assertIdentical('foobarbaz', Parser::label(0, $tokens));
	}

	public function testVariableName() {
		$code = <<<EOD
\$foo = 'bar';
EOD;
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$class = $tokens[0];
		$this->assertIdentical(T_CLASS, $class['id']);

		$this->assertIdentical(T_VARIABLE, $tokens[10]['id']);
		$this->assertTrue(in_array(10, $tokens[0]['children']));

		$this->assertIdentical(T_FUNCTION, $tokens[19]['id']);
		$this->assertTrue(in_array(19, $tokens[0]['children']));

		$this->assertIdentical(T_FUNCTION, $tokens[33]['id']);
		$this->assertTrue(in_array(33, $tokens[0]['children']));
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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$class = $tokens[0];
		$this->assertIdentical(T_CLASS, $class['id']);

		$this->assertIdentical(T_VARIABLE, $tokens[8]['id']);
		$this->assertTrue(in_array(8, $tokens[0]['children']));

		$this->assertIdentical(T_FUNCTION, $tokens[71]['id']);
		$this->assertTrue(in_array(71, $tokens[0]['children']));

		$this->assertIdentical(T_FUNCTION, $tokens[85]['id']);
		$this->assertTrue(in_array(85, $tokens[0]['children']));
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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$failure = false;
		foreach ($tokens as $tokenId => $token) {
			if ($tokens[$tokenId]['parent'] !== -1) {
				$parentId = $tokens[$tokenId]['parent'];
				$children = $tokens[$parentId]['children'];
				if (!in_array($tokenId, $children)) {
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

		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$while = $tokens[0];
		$this->assertIdentical(T_WHILE, $while['id']);
		$this->assertIdentical(-1, $tokens[0]['parent']);

		$if = $tokens[13];
		$this->assertIdentical(T_IF, $if['id']);
		$this->assertIdentical(-1, $tokens[13]['parent']);

		$foreach = $tokens[26];
		$this->assertIdentical(T_FOREACH, $foreach['id']);
		$this->assertIdentical(-1, $tokens[26]['parent']);

		$do = $tokens[39];
		$this->assertIdentical(T_DO, $do['id']);
		$this->assertIdentical(-1, $tokens[39]['parent']);

		$dowhile = $tokens[48];
		$this->assertIdentical(T_WHILE, $dowhile['id']);
		$this->assertIdentical(-1, $tokens[48]['parent']);
	}

	public function testNoParents() {
		$code = <<<EOD
return (!empty(\$name)) ? "{\$path}/{\$name}" : \$path;
EOD;
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		foreach ($tokens as $tokenId => $token) {
			$this->assertIdentical(-1, $tokens[$tokenId]['parent']);
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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertIdentical(39, count($tokens));
	}
	public function testDocBlockTooFarHasNoParent() {
		$code = <<<EOD
/**
 * Do I have a parent?
 */

class Rules {}
EOD;
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$docblock = $tokens[6];
		$this->assertIdentical(T_DOC_COMMENT, $docblock['id']);

		$parent = $tokens[$tokens[6]['parent']];
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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$abstract = $tokens[6];
		$this->assertIdentical(T_ABSTRACT, $abstract['id']);
		$this->assertIdentical(1, $tokens[6]['level']);

		$foo = $tokens[10];
		$this->assertIdentical(T_STRING, $foo['id']);
		$this->assertIdentical(2, $tokens[10]['level']);

		$public = $tokens[18];
		$this->assertIdentical(T_PUBLIC, $public['id']);
		$this->assertIdentical(1, $tokens[18]['level']);

		$bar = $tokens[22];
		$this->assertIdentical(T_STRING, $bar['id']);
		$this->assertIdentical(2, $tokens[22]['level']);
	}

	public function testIncompleteArrayException() {
		$expected = 'li3_quality\analysis\ParserException';
		$this->assertException($expected, function() {
			$code = <<<EOD
class Foobar {
	\$foo = array(
		'bar',
}
EOD;
			$tokenized = Parser::tokenize($code);
		});
	}

	public function testIncompleteDoWhile() {
		$expected = 'li3_quality\analysis\ParserException';
		$this->assertException($expected, function() {
			$code = <<<EOD
do {

} while()
EOD;
			$tokenized = Parser::tokenize($code);
		});
	}

	public function testAnonymousFunction() {
		$code = <<<EOD
return function() {
	return Parser::tokenize();
};
EOD;
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertIdentical(29, count($tokens));
	}

	public function testStaticDynamicVariableInsideExpression() {
		$code = <<<EOD
switch (\$case) {
	case 'case 1':
		if (static::\${\$var}) {
			echo 'hello';
		}
	break;
}
echo 'bar';
EOD;
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertIdentical(42, count($tokens));
		$this->assertIdentical(18, count($tokens[0]['children']));
		$this->assertIdentical(15, count($tokens[13]['children']));
		$this->assertIdentical(3, count($tokens[18]['children']));
	}

	public function testModifiers() {
		$code = <<<EOD
class Inflector {
	public static function rules() {
		static::\${\$var} = null;
	}
}
EOD;
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

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
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$this->assertIdentical(T_FUNCTION, $tokens[0]['id']);

		$params = Parser::parameters(0, $tokens);
		$this->assertIdentical(array(), $params);
	}

	public function testCorrectLevelWithFalsePositiveEndingParentheses() {
		$code = '$foo = "(foo{$bar})";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(12, count($tokens));
	}

	public function testCorrectLevelWithFalsePositiveEndingBracket() {
		$code = '$foo = "{foo{$bar}}";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(12, count($tokens));
	}

	public function testCorrectLevelWithFalsePositiveEndingParenWithVar() {
		$code = '$foo = "$a)";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(9, count($tokens));
	}

	public function testCorrectLevelWithFalsePositiveBeginningParenWithVar() {
		$code = '$foo = "$a(";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(9, count($tokens));
	}

	public function testCorrectLevelWithFalsePositiveEndingBracketWithVar() {
		$code = '$foo = "$a}";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(9, count($tokens));
	}

	public function testCorrectLevelWithFalsePositiveBeginningBracketWithVar() {
		$code = '$foo = "$a{";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(9, count($tokens));
	}

	public function testCorrectLevelWithOpeningParenthesesAtTheBeginning() {
		$code = '$foo = "($a";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(9, count($tokens));
	}

	public function testCorrectLevelWithClosingParenthesesAtTheBeginning() {
		$code = '$foo = ")$a";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(9, count($tokens));
	}

	public function testCorrectLevelWithOpeningBracketAtTheBeginning() {
		$code = '$foo = "}$a";';
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];
		$this->assertEqual(9, count($tokens));
	}

	public function testArrayChildren() {
		$code = <<<EOD
\$hello = \$foo ['bar'];
\$foo = array(
	array(
		'key1' => (\$variable1 && \$variable2),
		'key2' => \$var['hello']
	)
);
EOD;
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$this->assertIdentical(T_ARRAY_OPEN, $tokens[16]['id']);
		$this->assertTrue(in_array(19, $tokens[16]['children']));
		$this->assertCount(5, $tokens[16]['children']);

		$this->assertIdentical(T_ARRAY_OPEN, $tokens[19]['id']);
		$this->assertCount(24, $tokens[19]['children']);
	}

	public function testPhp54ArrayChildren() {
		$code = <<<EOD
\$hello = \$foo ['bar'];
\$foo = [
	[
		'key1' => (\$variable1 && \$variable2),
		'key2' => \$var ['hello']
	]
];
EOD;
		$tokenized = Parser::tokenize($code);
		$tokens = $tokenized['tokens'];

		$this->assertIdentical(T_SHORT_ARRAY_OPEN, $tokens[15]['id']);
		$this->assertTrue(in_array(17, $tokens[15]['children']));
		$this->assertCount(4, $tokens[15]['children']);

		$this->assertIdentical(T_SHORT_ARRAY_OPEN, $tokens[17]['id']);
		$this->assertCount(25, $tokens[17]['children']);
	}
}

?>