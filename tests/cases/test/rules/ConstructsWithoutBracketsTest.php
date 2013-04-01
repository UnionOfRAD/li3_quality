<?php

namespace li3_quality\tests\cases\test\rules;

class ConstructsWithoutBracketsTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\ConstructsWithoutBrackets';

	public function testCorrectConstruct() {
		$code = <<<EOD
require 'test.php';
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectConstructOnce() {
		$code = <<<EOD
require_once 'test.php';
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testParentheses() {
		$code = <<<EOD
require('test.php');
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testSpacedParentheses() {
		$code = <<<EOD
require ('test.php');
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testEchoWithCast() {
		$code = <<<EOD
echo (bool) \$var;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectBeginningParntheses() {
		$code = <<<EOD
require_once (!empty(\$name)) ? "{\$path}/{\$name}" : \$path;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectEndingParntheses() {
		$code = <<<EOD
exit false ? true : in_array(\$a, \$b);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectBeginningAndEndingParntheses() {
		$code = <<<EOD
print (false) ? (false===false) : (true===true);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testEmptyExit() {
		$code = <<<EOD
exit;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultilineConstruct() {
		$code = <<<EOD
echo array (
	'foo' => 'bar',
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testDoublEcho() {
		$code = <<<EOD
echo 'foo'; echo 'baz';
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testEmptyExist() {
		$code = <<<EOD
exit;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testRequireOnceWithWhiteSpace() {
		$code = <<<EOD
function foobar() {
	require_once 'foo.php';
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testEchoWithSingleNumber() {
		$code = <<<EOD
function foobar() {
	echo 1;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testReturnWithParentheses() {
		$code = <<<EOD
function foobar() {
	return (true ? "foo" : "bar");
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testReturnInOneLineFunction() {
		$code = <<<EOD
\$foo = function() { return true; };
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testExitWithParams() {
		$code = <<<EOD
exit(1);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncludeWithReturnValue() {
		$code = <<<EOD
\$config = include 'config.php';
EOD;

		$this->assertRulePass($code, $this->rule);
	}
}

?>