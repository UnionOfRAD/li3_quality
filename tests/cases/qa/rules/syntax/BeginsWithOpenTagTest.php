<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class BeginsWithOpenTagTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\BeginsWithOpenTag';

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