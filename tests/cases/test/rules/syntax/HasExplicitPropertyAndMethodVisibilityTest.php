<?php

namespace li3_quality\tests\cases\test\rules\syntax;

class HasExplicitPropertyAndMethodVisibilityTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\syntax\HasExplicitPropertyAndMethodVisibility';

	public function testNonClassFunctionsAndVariables() {
		$code = <<<EOD
\$foo = 'bar';
function bar() {
	return 'bar';
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testClassWithSetAccessMembers() {
		$code = <<<EOD
class foobar {
	public \$foo;
	protected \$baz = array();
	private function bar() {
		\$foo = 'bar';
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testClassWithNonDeclaredVariable() {
		$code = <<<EOD
class foobar {
	\$foo;
	private function bar() {
		\$foo = 'bar';
		return \$foo;
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testClassWithNonDeclaredMethod() {
		$code = <<<EOD
class foobar {
	public \$foo;
	function bar() {
		\$foo = 'bar';
		return \$foo;
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNestedMethodVariable() {
		$code = <<<EOD
class foobar {
	public function bar() {
		if (true) {
			while (\$var) {
				if (true) {
					\$foo = 'bar';
				} elseif (false) {
					\$bar = 'baz';
				}
				\$foobar = 'baz';
			}
		}
		\$foo = 'bar';
		return \$foo;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testAnonymousFunctionInsideMethod() {
		$code = <<<EOD
class foobar {
	public function lines(\$line = null) {
		\$lineEnding = function(\$source) {
			if (strpos(\$source, "\\r\\n") !== false) {
				return "\\r\\n";
			} elseif (strpos(\$source, "\\r") !== false) {
				return "\\r";
			} else {
				return "\\n";
			}
		};

		if (\$this->_lines === null) {
			\$this->_lines = explode(\$lineEnding(\$this->source()), \$this->source());
		}
		if (\$line) {
			return \$this->_lines[++\$line];
		}
		return \$this->_lines;
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testFileWithMultipleClasses() {
		$code = <<<EOD
class foobar {
	public function lines(\$line = null) {
		\$lineEnding = function(\$source) {
			if (strpos(\$source, "\\r\\n") !== false) {
				return "\\r\\n";
			} elseif (strpos(\$source, "\\r") !== false) {
				return "\\r";
			} else {
				return "\\n";
			}
		};

		if (\$this->_lines === null) {
			\$this->_lines = explode(\$lineEnding(\$this->source()), \$this->source());
		}
		if (\$line) {
			return \$this->_lines[++\$line];
		}
		return \$this->_lines;
	}
}
\$var = 'foo';
class barfoo {
	public \$cow = 'moo';
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testNoSpacingFailureVariable() {
		$code = "class foobar {\$foo = 'bar';}";
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNoSpacingPassVariable() {
		$code = "class foobar {public \$foo = 'bar';}";
		$this->assertRulePass($code, $this->rule);
	}

	public function testNoSpacingFailureMethod() {
		$code = "class foobar {public \$foo = 'bar'; function foo() { return 'bar'; }}";
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNoSpacingPassMethod() {
		$code = "class foobar {public \$foo = 'bar'; public function foo() { return 'bar'; }}";
		$this->assertRulePass($code, $this->rule);
	}

	public function testVariableWithStaticProperty() {
		$code = <<<EOD
class foobar {
	protected static \$_rules = array();
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testVariableWithStaticMethod() {
		$code = <<<EOD
class foobar {
	protected static function foo() {
		return 'bar';
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

}

?>