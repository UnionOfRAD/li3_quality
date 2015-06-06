<?php

namespace li3_quality\tests\cases\test\rules\syntax;

class HasCorrectInlineHTMLTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\syntax\HasCorrectInlineHTML';

	public function testCorrectInlineHTMLFormat() {
		$code = <<<EOD
<div>
Name: <?=\$name; ?>
</div>
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testInlineHTMLWithNoSpacing() {
		$code = <<<EOD
<div>
Name: <?=\$name;?>
</div>
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testInlineHTMLWithNoSemicolonWithSpace() {
		$code = <<<EOD
<div>
Name: <?=\$name ?>
</div>
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testInlineHTMLWithNoSemicolonWithOutSpace() {
		$code = <<<EOD
<div>
Name: <?=\$name?>
</div>
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testInlineHTMLWithExtraSpace() {
		$code = <<<EOD
<div>
Name: <?= \$name; ?>
</div>
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

}

?>