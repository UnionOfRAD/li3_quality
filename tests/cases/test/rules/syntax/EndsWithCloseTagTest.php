<?php

namespace li3_quality\tests\cases\test\rules\syntax;

class EndsWithCloseTagTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\syntax\EndsWithCloseTag';

	public function testWithClosingTag() {
		$code = <<<EOD
<?php
class foobar {}
?>
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testWithoutClosingTag() {
		$code = <<<EOD
<?php
class foobar {}
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

}

?>