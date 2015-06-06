<?php

namespace li3_quality\tests\cases\qa\rules\syntax;

class ClassesDoNotUseSuperglobalsDirectlyTest extends \li3_quality\test\Rule {

	public $rule = 'li3_quality\qa\rules\syntax\ClassesDoNotUseSuperglobalsDirectly';

	public function testClassWithoutSuperglobals() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
  }
}
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testSomethingThatLooksLikeASuperglobalButIsntIsFine() {
		$code = <<<EOD
<?php
class FooBarBaz {
  private \$GET = 1;
  protected \$_GETT = 4;
  protected \$_COOKIES = 'nom nom nom';

  public function foo() {
    print_r(\$_COOKIES);

    \$nothing = \$SERVER;
    echo \$GET + \$_GETT;
  }
}
EOD;
		$this->assertRulePass(array(
			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}

	public function testClassWithGet() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
    \$foo = \$_GET['foo'];
  }
}
EOD;
		$this->_shouldFail($code);
	}

	public function testClassWithServer() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
    \$foo = \$_SERVER['foo'];
  }
}
EOD;
		$this->_shouldFail($code);
	}

	public function testClassWithPost() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
    \$foo = \$_POST['foo'];
  }
}
EOD;
		$this->_shouldFail($code);
	}

	public function testClassWithEnv() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
    \$foo = \$_ENV['foo'];
  }
}
EOD;
		$this->_shouldFail($code);
	}

	public function testClassWithGlobals() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
    \$foo = \$GLOBALS['foo'];
  }
}
EOD;
		$this->_shouldFail($code);
	}

	public function testClassWithFiles() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
    print_r(\$_FILES);
  }
}
EOD;
		$this->_shouldFail($code);
	}

	public function testClassWithSession() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
    print_r(\$_SESSION);
  }
}
EOD;
		$this->_shouldFail($code);
	}

	public function testClassWithCookies() {
		$code = <<<EOD
<?php
class FooBarBaz {
  function foo() {
    print_r(\$_COOKIE);
  }
}
EOD;
		$this->_shouldFail($code);
	}

	protected function _shouldFail($code) {
		$this->assertRuleFail(array(

			'source' => $code,
			'wrap' => false,
		), $this->rule);
	}
}

?>