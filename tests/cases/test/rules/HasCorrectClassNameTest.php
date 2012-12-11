<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectClassNameTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\HasCorrectClassName';

	public function testWithClosingTag() {
		$code = <<<EOD
namespace bar/baz;
class FooBar {}
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'path' => '/bar/baz/FooBar.php',
		), $this->rule);
	}

	public function testWithoutClosingTag() {
		$code = <<<EOD
namespace bar/baz;
class foobar {}
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'path' => '/bar/baz/foobar.php',
		), $this->rule);
	}

	public function testWithoutMatchingFileName() {
		$code = <<<EOD
namespace bar/baz;
class FooBar {}
EOD;
		$this->assertRuleFail(array(
			'source' => $code,
			'path' => '/bar/baz/foo.php',
		), $this->rule);
	}

}

?>