<?php

namespace li3_quality\tests\cases\extensions\command;

use li3_quality\extensions\command\Syntax;
use li3_quality\tests\mocks\extensions\command\MockSyntax;
use lithium\console\Request;
use li3_quality\analysis\ParserException;

class SyntaxTest extends \li3_quality\test\Unit {

	protected $_backup = array();

	protected $_classes = array(
		'response' => 'lithium\tests\mocks\console\MockResponse',
		'libraries' => 'li3_quality\tests\mocks\core\MockLibraries',
		'dispatcher' => 'li3_quality\tests\mocks\test\MockDispatcher',
		'group' => 'li3_quality\tests\mocks\test\MockGroup',
		'rules' => 'li3_quality\tests\mocks\test\MockRules',
		'testable' => 'li3_quality\tests\mocks\test\MockTestable'
	);

	public function tearDown() {
		$reset = array('libraries', 'dispatcher', 'group', 'rules');

		foreach ($reset as $type) {
			$class = $this->_classes[$type];
			$class::reset();
		}
	}

	public function testSyntax() {
		$command = $this->_command();
		$result = $command->run(__DIR__);
		$this->assertEqual(true, $result);

		$result = $command->response->output;
		$rules = $this->_classes['rules'];

		$ruleCount = count($rules::get());
		$expected = "/Performing {$ruleCount} rules on path/";
		$this->assertPattern($expected, $result);
	}

	public function testSyntaxOutputOK() {
		$command = $this->_command();
		$result = $command->run();
		$this->assertEqual(true, $result);
		$output = $command->outputWithStyle;

		$this->assertEqual(5, count($output));
		list($msg, $style) = $output[4];
		$expected = "[OK] testables";
		$this->assertIdentical($expected, $msg);
		$this->assertIdentical("green", $style);
	}

	public function testSyntaxOutputFail() {
		$command = $this->_command();
		$rules = $this->_classes['rules'];
		$rules::$applyResponse = array(
			'success' => false, 'warnings' => array(),
			'violations' => array(array(
				'line' => "line",
				'position' => "position",
				'message' => "violation",
			))
		);
		$result = $command->run();
		$this->assertEqual(false, $result);
		$error = $command->errorWithStyle;

		$this->assertEqual(5, count($error));
		list($msg, $style) = $error[0];
		$expected = "[FAIL] testables";
		$this->assertIdentical($expected, $msg);
		$this->assertIdentical("red", $style);

		list($msg, $style) = $error[4];
		$expected = '/line\s+position\s+violation/';
		$this->assertPattern($expected, $msg);
		$this->assertIdentical("red", $style['style']);
	}

	public function testSyntaxOutputWarning() {
		$command = $this->_command();
		$rules = $this->_classes['rules'];
		$rules::$applyResponse = array(
			'success' => true,
			'warnings' => array(array(
				'line' => "line",
				'position' => "position",
				'message' => "violation",
			))
		);
		$result = $command->run();
		$this->assertEqual(true, $result);
		$output = $command->outputWithStyle;

		$this->assertEqual(9, count($output));
		list($msg, $style) = $output[8];
		$expected = '/line\s+position\s+violation/';
		$this->assertPattern($expected, $msg);
		$this->assertIdentical("yellow", $style['style']);
	}

	public function testSyntaxOutputParseError() {
		$command = $this->_command();
		$rules = $this->_classes['rules'];
		$rules::$applyResponse = function() {
			throw new ParserException("foobar");
		};
		$result = $command->run();
		$this->assertEqual(false, $result);
		$error = $command->errorWithStyle;

		$this->assertEqual(2, count($error));
		list($msg, $style) = $error[0];
		$expected = "[FAIL] testables";
		$this->assertIdentical($expected, $msg);
		$this->assertIdentical("red", $style);

		list($msg, $style) = $error[1];
		$this->assertIdentical("Parse error: foobar", $msg);
		$this->assertIdentical("red", $style);
	}

	public function testSyntaxOutputParseErrorVerbose() {
		$command = $this->_command();
		$command->verbose = true;
		$rules = $this->_classes['rules'];
		$rules::$applyResponse = function() {
			$exp = new ParserException("foobar");
			$exp->parserData = array('foo' => "bar");
			throw $exp;
		};
		$command->run();
		$error = $command->errorWithStyle;

		$this->assertEqual(3, count($error));
		list($msg, $style) = $error[2];
		$expected = print_r(array('foo' => "bar"), true);
		$this->assertIdentical($expected, $msg);
		$this->assertIdentical("red", $style);
	}

	public function testSubjects() {
		$command = $this->_command();
		$result = $command->subjects(__DIR__);

		$expected = array('testables');
		$this->assertIdentical($expected, $result);
	}

	public function testSubjectsSinglePath() {
		$command = $this->_command();
		$result = $command->subjects(__FILE__);

		$expected = array('testables');
		$this->assertIdentical($expected, $result);
	}

	public function testSyntaxFiltersFromLibraryFile() {
		$command = $this->_command();
		$libraries = $this->_classes['libraries'];
		$rules = $this->_classes['rules'];
		$path = sys_get_temp_dir();
		$this->skipIf(!is_writable($path), "Path `{$path}` is not writable.");

		$libraries::add('test_library', compact('path'));
		$json = array(
			'rules' => array("from", "file"),
			'variables' => array("file variables")
		);
		mkdir($path . '/test');
		$tmpfile = $path . '/test/rules.json';
		file_put_contents($tmpfile, json_encode($json));
		$command->library = 'test_library';

		$result = $command->invokeMethod('_syntaxFilters');
		$expected = array('from', 'file');
		$this->assertIdentical($expected, $result);

		$this->assertEqual(1, count($rules::$invocations));
		$call = $rules::$invocations[0];
		$this->assertEqual('ruleOptions', $call['method']);
		$this->assertEqual(array('file variables'), $call['args'][0]);

		unlink($tmpfile);
		rmdir($path . '/test');
		$libraries::remove('test_library');
	}

	public function testSyntaxFiltersFromDefaultFile() {
		$command = $this->_command();
		$rules = $this->_classes['rules'];

		$result = $command->invokeMethod('_syntaxFilters');
		$this->assertTrue(is_array($result));

		$this->assertEqual(1, count($rules::$invocations));
		$call = $rules::$invocations[0];
		$this->assertEqual('ruleOptions', $call['method']);
	}

	protected function _command(array $options = array()) {
		$request = $this->_request($options);
		$classes = $this->_classes;
		return new MockSyntax(compact('request', 'classes'));
	}

	protected function _request(array $options = array()) {
		$options += array(
			'params' => array(), 'input' => fopen('php://temp', 'w+')
		);
		$params = $options['params'];
		unset($options['params']);
		$request = new Request($options);
		$request->params += $params;
		return $request;
	}
}

?>