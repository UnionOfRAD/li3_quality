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
\$arr = array(
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

}

?>