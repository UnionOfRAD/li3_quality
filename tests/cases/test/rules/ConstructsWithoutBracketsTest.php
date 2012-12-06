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

	public function testReturnWithCast() {
		$code = <<<EOD
return (bool) \$var;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectBeginningParntheses() {
		$code = <<<EOD
return (!empty(\$name)) ? "{\$path}/{\$name}" : \$path;
EOD;
		$this->assertRulePass($code, $this->rule);
	}


	public function testCorrectEndingParntheses() {
		$code = <<<EOD
return false ? true : in_array(\$a, \$b);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testCorrectBeginningAndEndingParntheses() {
		$code = <<<EOD
return (false) ? (false===false) : (true===true);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testEmptyReturn() {
		$code = <<<EOD
return;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultilineConstruct() {
		$code = <<<EOD
return	array (
	'foo' => 'bar',
);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testConstructsNotOnOwnLine() {
		$code = <<<EOD
if(!\$this->service(\$service)) return false;
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

}

?>