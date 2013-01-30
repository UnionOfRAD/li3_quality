<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectTabIndentionTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectTabIndention';

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
	}

	public function testIncorrectMultiLineString() {
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

}

?>