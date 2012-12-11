<?php

namespace li3_quality\tests\cases\test\rules;

class BeginsWithOpenTagTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\BeginsWithOpenTag';

	public function testWithTag() {
		$code = <<<EOD
<?php
echo 'foobar';
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testWithoutTag() {
		$code = <<<EOD
<title>Foobar</title>
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

}

?>