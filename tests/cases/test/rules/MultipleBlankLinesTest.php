<?php

namespace li3_quality\tests\cases\test\rules;

class MultipleBlankLinesTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\MultipleBlankLines';

	public function testNoDoubleLines() {
		$code = <<<EOD
class foo {

	public function bar() {

	}

}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testBeginningDoubleLine() {
		$code = <<<EOD


class foo {

	public function bar() {

	}

}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testEndingDoubleLine() {
		$code = <<<EOD
class foo {

	public function bar() {

	}

}


EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMiddleDoubleLine() {
		$code = <<<EOD
class foo {

	public function bar() {


	}

}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testDoubleLineInHeredoc() {
		$code = <<<EOD
<<<EOT


EOT;
EOD;
		$this->assertRulePass($code, $this->rule);
	}

}

?>