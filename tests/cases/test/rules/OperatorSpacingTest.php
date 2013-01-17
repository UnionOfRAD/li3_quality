<?php

namespace li3_quality\tests\cases\test\rules;

class OperatorSpacingTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\OperatorSpacing';

	public function testSomethingEqualWithSpacing() {
		$code = '$i += 1;';
		$this->assertRulePass($code, $this->rule);
	}

	public function testTooMuchLeftOperatorSpacing() {
		$code = '$i  += 1;';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testTooMuchRightOperatorSpacing() {
		$code = '$i +=  1;';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testTooMuchOperatorSpacing() {
		$code = '$i  +=   1;';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testSomethingEqualWithoutSpacing() {
		$code = '$i+=1;';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testSomethingEqualWithBeginningSpacing() {
		$code = '$i +=1;';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testSomethingEqualWithEndingSpacing() {
		$code = '$i+= 1;';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testStaticMethodCallWithNoSpacing() {
		$code = 'MyClass::method();';
		$this->assertRulePass($code, $this->rule);
	}

	public function testStaticMethodCallWithEndingSpacing() {
		$code = 'MyClass:: method();';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testStaticMethodCallWithBeginningSpacing() {
		$code = 'MyClass:: method();';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testStaticMethodCallWithSpacing() {
		$code = 'MyClass :: method();';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNonStaticMethodCallWithNoSpacing() {
		$code = '$object->method();';
		$this->assertRulePass($code, $this->rule);
	}

	public function testNonStaticMethodCallWithEndingSpacing() {
		$code = '$object-> method();';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNonStaticMethodCallWithBeginningSpacing() {
		$code = '$object ->method();';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNonStaticMethodCallWithSpacing() {
		$code = '$object -> method();';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testLogicalOperatorsWithSpacing() {
		$code = '1 && 2';
		$this->assertRulePass($code, $this->rule);
	}

	public function testLogicalOperatorWithoutSpacing() {
		$code = 'true||false';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testLogicalOperatorWithoutBeginningSpacing() {
		$code = 'true !==false';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testLogicalOperatorWithoutEndingSpacing() {
		$code = 'true=== false';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMultiLineOperatorsWithCorrectSpacing() {
		$code = <<<EOD
class foo {
	public function bar() {
		\$baz = 'foobar';
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultiLineOperatorsWithNoSpacing() {
		$code = <<<EOD
class foo {
	public function bar() {
		foo::bar();
	}
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultiLineOperatorsWithInCorrectSpacing() {
		$code = <<<EOD
class foo {
	public function bar() {
		\$baz= 'foobar';
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMultiLineOperatorsWithIncorrectNoSpacing() {
		$code = <<<EOD
class foo {
	public function bar() {
		foo:: bar();
	}
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testArrayAlignment() {
		$code = <<<EOD
\$foo = array(
	'bar'    => true,
	'foobar' => false,
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testTermOperatorTypical() {
		$code = <<<EOD
\$var = true ? false : true;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testTermOperatorShorthand() {
		$code = <<<EOD
\$var = true ?: false;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testWithOpeningPHPTag() {
		$code = <<<EOD
<?php
\$var = true ?: false;
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testShortHandTermWithNoSpace() {
		$code = <<<EOD
<?php
\$var = true?:false;
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testTermWithNoSpace() {
		$code = <<<EOD
<?php
\$var = true?false:true;
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testArrayIndex() {
		$code = '$i = $m[1+10];';
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectArrayIndex() {
		$code = '$i = $m[1 + 10];';
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectStringConcat() {
		$code = <<<EOD
\$arr = 'one' .
	'two';
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testNegativeNumber() {
		$code = 'return $line === 0 ? -1 : 1;';
		$this->assertRulePass($code, $this->rule);
	}

	public function testNegativeNumberInsideParentheses() {
		$code = 'return max(-1, 1);';
		$this->assertRulePass($code, $this->rule);
	}

	public function testTermVariableEqualsStrings() {
		$code = '$hasTest ? \'has test\' : \'no test\'';
		$this->assertRulePass($code, $this->rule);
	}

	public function testTermVariableEqualsStringsInArray() {
		$code = <<<EOD
\$arr = array(
	1,
	\$hasTest ? 'has test' : 'no test',
	2,
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testNegativeReturn() {
		$code = 'return -10;';
		$this->assertRulePass($code, $this->rule);
	}

	public function testDecimalInString() {
		$code = '$foo = "bar.";';
		$this->assertRulePass($code, $this->rule);
	}

	public function testDecimalInBiggerString() {
		$code = '$this->stop(0, "Could not find any files in {$library}.");';
		$this->assertRulePass($code, $this->rule);
	}

	public function testSwitchStatement() {
		$code = <<<EOD
switch(1) {
	case 2:
		return false;
	default:
		return true;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testAssignByReference() {
		$code = <<<EOD
\$foo =& \$bar;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testDivision() {
		$code = '$a = 10 / 3;';
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultilineExpression() {
		$code = <<<EOD
\$foo = true
	&& false;
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMultilineAssignment() {
		$code = <<<EOD
\$foo =
	true ?: false;
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMultilineArrayAssignment() {
		$code = <<<EOD
\$foo = array(
	'bar' =>
		'baz'
);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMultilineTernary() {
		$code = <<<EOD
\$foo = true
	? true : false;
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testNegativeAsUnary() {
		$code = '$foo = -$bar;';
		$this->assertRulePass($code, $this->rule);
	}
}

?>