<?php

namespace li3_quality\tests\cases\test\rules\syntax;

class HasCorrectTabIndentionTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\syntax\HasCorrectTabIndention';

	public function testCorrectNoTabs() {
		$code = <<<EOD
\$a = true;
array('bar' => 'baz');
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectNoTabs() {
		$code = <<<EOD
\$a = true;
	array('bar' => 'baz');
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectArrayTabbing() {
		$code = <<<EOD
\$foo = array(
	'bar',
	'baz',
	'foobar',
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectArrayTabbing() {
		$code = <<<EOD
\$foo = array(
'bar',
		'baz',
	'foobar',
);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectShortArrayTabbing() {
		$code = <<<EOD
\$foo = [
	'bar',
	'baz',
	'foobar',
];
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectShortArrayTabbing() {
		$code = <<<EOD
\$foo = [
'bar',
		'baz',
	'foobar',
];
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectFunctionTabbing() {
		$code = <<<EOD
function foo() {
	\$i = 10;
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectFunctionTabbing() {
		$code = <<<EOD
function foo() {
		\$i = 10;
return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectBasicClass() {
		$code = <<<EOD
class foo {
	\$baz = array(
		20,
		24,
		26,
	);
	public function bar() {
		\$i = 10;
		\$arr = array(32);
		\$arr = array(
			'jim',
			'joe',
			'bob',
		);
		\$arr = foo::bar(array(
			'joe', 'bob', 'jim',
		));
		return \$i;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testBasicClassWithBadPropertyTabbing() {
		$code = <<<EOD
class foo {
	\$baz = array(
		20,
			24,
		26,
	);
	public function bar() {
		\$i = 10;
		\$arr = array(32);
		\$arr = array(
			'jim',
			'joe',
			'bob',
		);
		\$arr = foo::bar(array(
			'joe', 'bob', 'jim',
		));
		return \$i;
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testBasicClassWithBadMethodContentTabbing() {
		$code = <<<EOD
class foo {
	\$baz = array(
		20,
		24,
		26,
	);
	public function bar() {
		\$i = 10;
			\$arr = array(32);
		\$arr = array(
			'jim',
			'joe',
			'bob',
		);
		\$arr = foo::bar(array(
			'joe', 'bob', 'jim',
		));
		return \$i;
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testBasicClassWithBadMethodContentValueTabbing() {
		$code = <<<EOD
class foo {
	\$baz = array(
		20,
		24,
		26,
	);
	public function bar() {
		\$i = 10;
		\$arr = array(32);
		\$arr = array(
			'jim',
		'joe',
			'bob',
		);
		\$arr = foo::bar(array(
			'joe', 'bob', 'jim',
		));
		return \$i;
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectElseIfStatement() {
		$code = <<<EOD
if (true) {
	// do something
} elseif (true) {
	// do something elseif
} else {
	// do something else
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testElseIfStatement() {
		$code = <<<EOD
if (true) {
	// do something
	} elseif (true) {
		// do something elseif
		} else {
			// do something else
		}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testHereDoc() {
		$code = <<<EOD
function foo() {
	return <<<EOR
array(
	foo,
	bar,
	baz,
);
EOR;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectMultiLineString() {
		$code = <<<EOD
\$demo = 'abcdef' .
	'ghi';
\$bar = false;
EOD;
		$this->assertRulePass($code, $this->rule);

		$code = <<<EOD
\$demo = 'abcdef' .
	     'ghi';
\$bar = false;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectMultiLineStringWithTwoTabs() {
		$code = <<<EOD
\$demo = 'abcdef' .
		 'ghi';
\$bar = false;
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIgnoreDocBlocks() {
		$code = <<<EOD
true;
function foo() {
	/**
	 * This is a correctly tabbed docblock
	 */
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreBadSpacedDocBlocks() {
		$code = <<<EOD
true;
function foo() {
	/**
		* This is a incorrectly tabbed docblock
	 */
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreBadSpacedHashComment() {
		$code = <<<EOD
true;
function foo() {
# this is a comment
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreBadSpacedForwardSlashComment() {
		$code = <<<EOD
true;
function foo() {
		// this is a comment
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultiDimensionalArray() {
		$code = <<<EOD
\$code = array(
	array(
		1,
		2,
		3,
	),
	array(
		4,
		5,
		6,
	),
),
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectTabSpacing() {
		// This should be covered in a different rule, not this one
		$code = <<<EOD
array(
	'foobar' => 'bar',
	'foo'	 => 'bar',
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectBasicClassWithBlankSpacing() {
		$code = <<<EOD
class foo {
	\$baz = array(
		20,
		24,
		26,
	);

	public function bar() {
		\$i = 10;

		\$arr = array(32);
		\$arr = array(
			'jim',
			'joe',
			'bob',
		);

		\$arr = foo::bar(array(
			'joe', 'bob', 'jim',
		));
		return \$i;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectTabIndentionWithSwitch() {
		$code = <<<EOD
switch (true) {
	case (\$formatter instanceof Closure):
		return \$formatter(\$message);
	case (is_string(\$formatter)):
		\$data = \$this->_message_data(\$message);
		return String::insert(\$formatter, \$data);
	default:
		throw new RuntimeException(
			"Formatter for format `{\$format}` is neither string nor closure."
		);
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testComlpexSwitch() {
		$code = <<<EOD
switch (true) {
	case 1:
		return 2;
	case 3:
		switch(false) {
			case 4:
				return 3;
			case 5:
				return 7;
			case 8:
			break;
		}
	break;
	default:
		echo false;
	break;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testBrokenComplexSwitch() {
		$code = <<<EOD
switch (true) {
	case 1:
		return 2;
	case 3:
		switch (true) {
			case 1:
				return 2;
			case 3:
				break;
			default:
				echo false;
				break;
			}
		break;
	default:
		echo false;
		break;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectTryCatch() {
		$code = <<<EOD
try {
	return true;
} catch(\Exception \$e) {
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testAlternateSyntaxIf() {
		$code = <<<EOD
if (false):
	echo 'foo';
endif;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testAlternateSyntaxIfComplex() {
		$code = <<<EOD
if (false):
	echo 'foo';
elseif (true):
	echo 'bar';
else:
	echo 'baz';
endif;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoresIndentedDocblocks() {
		$code = <<<EOD
class Unit {
	/**
	 * The rule that is being tested against.
	 */
	public \$rule = null;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultiLineChaining() {
		$code = <<<EOD
\$this->assertTrue(\$chain->called('method1')
	->called('method2')->with('bar')
	->called('method1')
	->success());
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultiLineChainingWithArrays() {
		$code = <<<EOD
\$result = \$chain->called('method1')
	->called('method2')
	->with('first', array(
		'id' => 100
	))
	->success();
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMutlilineAssignment() {
		$code = <<<EOD
return true ||
	false &&
	true AND
	true OR
	false;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testFailingTabIndentPropagation() {
		$code = <<<EOD
\$array = array(
	'level1' => array(
		'level2' => array(
			'level3' => array(
))));
echo 'foo';
EOD;
		$this->assertRuleFail($code, $this->rule);
		$this->assertCount(1, $this->rule->violations());
	}

	public function testIgnoreBracesInString() {
		$code = <<<EOD
\$code = 'hello(';
echo bob;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testClosureAsParameter() {
		$code = <<<EOD
return \$this->_filter(__METHOD__, \$args, function(\$self, \$args) {
	\$variable = \$this->_protected;
	if (isset(\$this->_path[\$variable])) {
		return \$this->path[\$variable];
	}
});
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMutlilineConditions() {
		$code = <<<EOD
\$expression = (isset(\$variable[\$key]) && (
	\$class instanceof static::\$_classes['item'] ||
	\$condtions
));
EOD;
		$this->assertRulePass($code, $this->rule);

		$code = <<<EOD
\$expression = (
	\$var1 &&
	\$var2 ||
	\$var3
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMutlilineConditionsWithArrays() {
		$code = <<<EOD
\$isAvailable = (
	Connections::get('test', array('config' => true)) &&
	Connections::get('test')->isConnected(array('autoConnect' => true))
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCurlyBraceSyntax() {
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
		$this->assertRulePass($code, $this->rule);
	}

	public function testIndentInArrayWithContact() {
		$code = <<<EOD
\$data = array(
	'key'           => 'value',
	'very long key' => 'hello world',
	'multiline'     => 'qjfkqfkqslkfqjfjqsdfklsmqflksjfsdfsqdkfksqfl' .
	                   'kfjklqsjflkqskjfklqsfsqkdfjkqlsjfklsqkfsk' .
	                   'kfjklqsjflkqskjfklqsfsqkdfjkqlsjfklsqkfsk'.
	                   'kfjklqsjflkqskjfklqsfsqkdfjkqlsjfklsqkfsk'
	'last'          => 'end',
	'mult => line2' => 'qjfkqfkqslfjqsdfklsmqflksjfsdfsqdkfksqfl' .
	                   'kfjklqsjqlsjfklsqkfsk',
	'multline3'     => 'qjfkqfkqslf => jqsdfklsmqflksjfsdfsqdkfksqfl' .
	                   'kfjklqsjqlsjfklsqkfsk'
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIndentInArrayWithOr() {
		$code = <<<EOD
\$data = array(
	'key'           => 'value',
	'very long key' => (\$variable && \$variable2 && \$variable2) ||
	                   (\$variable3 && \$variable4 && \$variable5)
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectIndentationInArray() {
		$code = <<<EOD
\$data = array(
	'key'           => 'value',
	'very long key' => 'hello world',
	'multiline'     => 'qjfkqfkqslkfqjfjqsdfklsmqflksjfsdfsqdkfksqfl' .
		'kfjklqsjflkqskjfklqsfsqkdfjkqlsjfklsqkfsk'
);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIgnoreIndentInConstantStrings() {
		$code = <<<EOD
file_put_contents("filename", "
	<?php echo 'this is content'; ?" . ">
	<?='This is
		indented content
		that breaks over
		several lines
	'; ?>
");
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreIndentInEncapsedStrings() {
		$code = <<<EOD
file_put_contents("filename", "
	<?php echo 'this is content'; ?" . ">
	<?='This is
		indented \$content
		that breaks over
		\$several lines
	'; ?>
");
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreIndentInHeredoc() {
		$code = <<<EOD
if (true) {
	\$data = <<<EOT
\$hello = 'world !';
EOT;
}

EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIgnoreBracketsInStrings() {
		$code = <<<EOD
if (true) {
	\$result = "(\$var) ";
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIndentWithSpacesInArrays() {
		$code = <<<EOD
\$headers = array(
	'X-Wf-Protocol-1' => 'http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
	'X-Wf-1-Plugin-1' =>
	    'http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
	'X-Wf-1-Structure-1' =>
	    'http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1'
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectMixedControlStructureTabIndent() {

		$controls = array(
			'if' => 'true',
			'while' => '$variable',
			'for' => '$i=0; $i++; $i<10',
			'foreach' => '$array as $key => $value'
		);

		foreach ($controls as $key => $value) {
			$code = <<<EOD
$key ($value) { \$count++; }

$key ($value) {
	\$count++;
}

$key ($value)
{
	\$count++;
}

$key (
	$value
) {
	\$count++;
}

$key
(
	$value
) {
	\$count++;
}

$key
(
	$value
)
{
	\$count++;
}
EOD;
		}

		$this->assertRulePass($code, $this->rule);
	}

	public function testBracketsInStringsIsIgnored() {
		$code = <<<EOD
if (true) {
	\$pattern = ":{\$this->_subPatterns[\$key]}";

	if (true) {
		continue;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testEndingCommentOnLine() {
		$code = <<<EOD
class MyClass {
	if(true) {
		break; //default
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testBracketsAreNotParentsInStrings() {
		$code = <<<EOD
if (true) {
	\$hello = "{\$trace}[{\$key}]";
	\$world = \$result[\$key];
	if (true) {
		break;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}
}

?>