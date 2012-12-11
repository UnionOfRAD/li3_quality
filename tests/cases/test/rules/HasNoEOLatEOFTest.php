<?php

namespace li3_quality\tests\cases\test\rules;

class HasNoEOLatEOFTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasNoEOLatEOF';

	public function testSimpleWithNewLine() {
		$code = <<<EOD
<?php
echo 'foo';
?>

EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testSimpleWithoutNewLine() {
		$code = <<<EOD
<?php
echo 'foo';
?>
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

}

?>