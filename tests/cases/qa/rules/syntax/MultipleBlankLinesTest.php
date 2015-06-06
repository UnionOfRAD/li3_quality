<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class MultipleBlankLinesTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\MultipleBlankLines';

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