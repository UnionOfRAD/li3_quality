<?php

namespace li3_quality\tests\cases\extensions\command;

use li3_quality\extensions\command\Quality;
use li3_quality\tests\mocks\extensions\command\MockQuality;
use lithium\console\Request;
use li3_quality\analysis\ParserException;

class QualityTest extends \li3_quality\test\Unit {
	protected $_backup = array();

	protected $_classes = array(
		'response' => 'lithium\tests\mocks\console\MockResponse',
		'libraries' => 'li3_quality\tests\mocks\core\MockLibraries',
		'dispatcher' => 'li3_quality\tests\mocks\test\MockDispatcher',
		'group' => 'li3_quality\tests\mocks\test\MockGroup',
		'rules' => 'li3_quality\tests\mocks\test\MockRules',
		'testable' => 'li3_quality\tests\mocks\test\MockTestable'
	);

	public function setUp() {
		$this->_backup['cwd'] = getcwd();
		$this->_backup['_SERVER'] = $_SERVER;
		$_SERVER['argv'] = array();
	}

	public function tearDown() {
		$reset = array('libraries', 'dispatcher', 'group', 'rules');
		foreach ($reset as $type) {
			$class = $this->_classes[$type];
			$class::reset();
		}
		$_SERVER = $this->_backup['_SERVER'];
		chdir($this->_backup['cwd']);
	}

	public function checkDefaultInput() {
		$quality = $this->_quality();
		$this->assertIdentical(true, $quality->library);
		$this->assertIdentical(100, $quality->threshold);
		$expected = 'resources|webroot|vendor|libraries';
		$this->assertIdentical($expected, $quality->exclude);
	}

	public function testOverwriteDefaults() {
		$params = array(
			'library' => 'library', 'threshold' => 80, 'exclude' => 'exclude'
		);
		$quality = $this->_quality(compact('params'));

		$this->assertIdentical('library', $quality->library);
		$this->assertIdentical(80, $quality->threshold);
		$this->assertIdentical('exclude', $quality->exclude);
	}

	public function testHelp() {
		$request = $this->_request();
		$classes = $this->_classes;
		$quality = new Quality(compact('request', 'classes'));
		$quality->run();
		$result = $quality->response->output;

		$this->assertPattern("/li3 quality syntax/", $result);
		$this->assertPattern("/li3 quality documented/", $result);
		$this->assertPattern("/li3 quality coverage/", $result);
	}

	public function testSyntax() {
		$quality = $this->_quality();
		$result = $quality->syntax();
		$this->assertEqual(true, $result);

		$result = $quality->response->output;
		$rules = $this->_classes['rules'];

		$ruleCount = count($rules::get());
		$expected = "/Performing {$ruleCount} rules on 1 classes\./";
		$this->assertPattern($expected, $result);
	}

	public function testSyntaxSetsOptionsWhenGit() {
		$env = array('GIT_DIR' => "git-dir");
		$quality = $this->_quality(compact('env'));
		$quality->syntax();

		$this->assertIdentical(true, $quality->plain);
		$this->assertIdentical(true, $quality->silent);
	}

	public function testSyntaxOutputOK() {
		$quality = $this->_quality();
		$result = $quality->syntax();
		$this->assertEqual(true, $result);
		$output = $quality->outputWithStyle;

		$this->assertEqual(5, count($output));
		list($msg, $style) = $output[4];
		$expected = "[OK] testables";
		$this->assertIdentical($expected, $msg);
		$this->assertIdentical("green", $style);
	}

	public function testSyntaxOutputFail() {
		$quality = $this->_quality();
		$rules = $this->_classes['rules'];
		$rules::$applyResponse = array(
			'success' => false, 'warnings' => array(),
			'violations' => array(array(
				'line' => "line",
				'position' => "position",
				'message' => "violation",
			))
		);
		$result = $quality->syntax();
		$this->assertEqual(false, $result);
		$error = $quality->errorWithStyle;

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
		$quality = $this->_quality();
		$rules = $this->_classes['rules'];
		$rules::$applyResponse = array(
			'success' => true,
			'warnings' => array(array(
				'line' => "line",
				'position' => "position",
				'message' => "violation",
			))
		);
		$result = $quality->syntax();
		$this->assertEqual(true, $result);
		$output = $quality->outputWithStyle;

		$this->assertEqual(9, count($output));
		list($msg, $style) = $output[8];
		$expected = '/line\s+position\s+violation/';
		$this->assertPattern($expected, $msg);
		$this->assertIdentical("yellow", $style['style']);
	}

	public function testSyntaxOutputParseError() {
		$quality = $this->_quality();
		$rules = $this->_classes['rules'];
		$rules::$applyResponse = function() {
			throw new ParserException("foobar");
		};
		$result = $quality->syntax();
		$this->assertEqual(false, $result);
		$error = $quality->errorWithStyle;

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
		$quality = $this->_quality();
		$quality->verbose = true;
		$rules = $this->_classes['rules'];
		$rules::$applyResponse = function() {
			$exp = new ParserException("foobar");
			$exp->parserData = array('foo' => "bar");
			throw $exp;
		};
		$quality->syntax();
		$error = $quality->errorWithStyle;

		$this->assertEqual(3, count($error));
		list($msg, $style) = $error[2];
		$expected = print_r(array('foo' => "bar"), true);
		$this->assertIdentical($expected, $msg);
		$this->assertIdentical("red", $style);
	}

	public function testDocumented() {
		$quality = $this->_quality();
		$quality->documented();

		$expected = "/Checking documentation on 1 classes\./";
		$result = $quality->response->output;
		$this->assertPattern($expected, $result);
	}

	public function testCoverage() {
		$quality = $this->_quality();
		$quality->coverage();

		$expected = "/Checking coverage on 1 classes\./";
		$result = $quality->response->output;
		$this->assertPattern($expected, $result);
	}

	public function testCoverageGreen() {
		$quality = $this->_quality();
		$dispatcher = $this->_classes['dispatcher'];
		$dispatcher::$coverage = array(
			'testables' => array('percentage' => 86)
		);
		$quality->coverage();
		$output = $quality->outputWithStyle;

		$this->assertEqual(5, count($output));
		list($msg, $style) = $output[3];
		$expected = "Checking coverage on 1 classes.";
		$this->assertIdentical($expected, $msg);

		list($msg, $style) = $output[4];
		$expected = '/has test\s+\|\s+86\.00%\s+\|\s+testables/';
		$this->assertPattern($expected, $msg);
		$this->assertIdentical("green", $style);
	}

	public function testCoverageRed() {
		$quality = $this->_quality();
		$dispatcher = $this->_classes['dispatcher'];
		$dispatcher::$coverage = array(
			'testables' => array('percentage' => 0)
		);
		$quality->coverage();
		$output = $quality->outputWithStyle;

		$this->assertEqual(5, count($output));
		list($msg, $style) = $output[3];
		$expected = "Checking coverage on 1 classes.";
		$this->assertIdentical($expected, $msg);

		list($msg, $style) = $output[4];
		$expected = '/has test\s+\|\s+0\.00%\s+\|\s+testables/';
		$this->assertPattern($expected, $msg);
		$this->assertIdentical("red", $style);
	}

	public function testCoverageYellow() {
		$quality = $this->_quality();
		$dispatcher = $this->_classes['dispatcher'];
		$dispatcher::$coverage = array(
			'testables' => array('percentage' => 42)
		);
		$quality->coverage();
		$output = $quality->outputWithStyle;

		$this->assertEqual(5, count($output));
		list($msg, $style) = $output[3];
		$expected = "Checking coverage on 1 classes.";
		$this->assertIdentical($expected, $msg);

		list($msg, $style) = $output[4];
		$expected = '/has test\s+\|\s+42\.00%\s+\|\s+testables/';
		$this->assertPattern($expected, $msg);
		$this->assertIdentical("yellow", $style);
	}

	public function testCoverageNoTest() {
		$quality = $this->_quality();
		$group = $this->_classes['group'];
		$group::$all = array();
		$quality->coverage();
		$output = $quality->outputWithStyle;

		$this->assertEqual(5, count($output));
		list($msg, $style) = $output[3];
		$expected = "Checking coverage on 1 classes.";
		$this->assertIdentical($expected, $msg);

		list($msg, $style) = $output[4];
		$expected = '/no test\s+\|\s+n\/a\s+\|\s+testables/';
		$this->assertPattern($expected, $msg);
		$this->assertIdentical("red", $style);
	}

	public function testTestables() {
		$quality = $this->_quality();
		$quality->mockTestables = false;
		$libraries = $this->_classes['libraries'];

		$result = $quality->invokeMethod('_testables');
		$expected = array('testables');
		$this->assertIdentical($expected, $result);

		$this->assertEqual(1, count($libraries::$invocations));
		$call = $libraries::$invocations[0];
		list($library, $options) = $call['args'];
		$this->assertEqual('find', $call['method']);
		$this->assertEqual($quality->library, $library);
		$this->assertEqual(true, $options['recursive']);
		$expected = '/' . $quality->exclude . '/';
		$this->assertEqual($expected, $options['exclude']);
	}

	public function testTestablesOptions() {
		$quality = $this->_quality();
		$quality->mockTestables = false;
		$libraries = $this->_classes['libraries'];

		$opts = array('recursive' => false, 'exclude' => 'exclude');
		$result = $quality->invokeMethod('_testables', array($opts));

		$this->assertEqual(1, count($libraries::$invocations));
		$call = $libraries::$invocations[0];
		list($library, $options) = $call['args'];
		$this->assertEqual('find', $call['method']);
		$this->assertEqual(false, $options['recursive']);
		$expected = '/' . $quality->exclude . '|exclude/';
		$this->assertEqual($expected, $options['exclude']);

		$libraries::reset();
		$quality->exclude = NULL;

		$opts = array('exclude' => 'exclude');
		$result = $quality->invokeMethod('_testables', array($opts));
		$this->assertEqual(1, count($libraries::$invocations));
		$call = $libraries::$invocations[0];
		list($library, $options) = $call['args'];
		$this->assertEqual('/exclude/', $options['exclude']);

		$libraries::reset();

		$result = $quality->invokeMethod('_testables');
		$this->assertEqual(1, count($libraries::$invocations));
		$call = $libraries::$invocations[0];
		list($library, $options) = $call['args'];
		$this->assertFalse(array_key_exists('exclude', $options));
	}

	public function testTestablesPathSetsLibrary() {
		$quality = $this->_quality();
		$quality->mockTestables = false;
		$quality->library = "lib1";
		$quality->pathResponse = "lib2\path-response";
		$opts = array('path' => "required");
		$quality->invokeMethod('_testables', array($opts));
		$libraries = $this->_classes['libraries'];
		$call = $libraries::$invocations[0];
		list($library, $options) = $call['args'];
		$this->assertEqual("lib2", $library);
		$this->assertEqual("/path-response", $options['path']);
	}

	public function testTestablesSinglePath() {
		$quality = $this->_quality();
		$quality->mockTestables = false;
		$quality->pathResponse = "path-response";
		$opts = array('path' => "foo.php");
		$result = $quality->invokeMethod('_testables', array($opts));
		$expected = array("path-response");
		$this->assertIdentical($expected, $result);
	}

	public function testTestablesNoneFound() {
		$quality = $this->_quality();
		$quality->mockTestables = false;
		$quality->library = "foobar";
		$libraries = $this->_classes['libraries'];
		$libraries::$findResponse = array();
		$quality->invokeMethod('_testables');
		$this->assertEqual(1, count($quality->stops));
		$stop = $quality->stops[0];
		$this->assertEqual(0, $stop[0]);
		$this->assertEqual("Could not find any files in foobar.", $stop[1]);
	}

	public function testSyntaxFiltersFromArray() {
		$quality = $this->_quality();
		$quality->filters = array('from', 'array');
		$result = $quality->invokeMethod('_syntaxFilters');
		$expected = array('from', 'array');
		$this->assertIdentical($expected, $result);
	}

	public function testSyntaxFiltersFromString() {
		$quality = $this->_quality();
		$quality->filters = 'from, string';
		$result = $quality->invokeMethod('_syntaxFilters');
		$expected = array('from', 'string');
		$this->assertIdentical($expected, $result);
	}

	public function testSyntaxFiltersFromLibraryFile() {
		$quality = $this->_quality();
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
		$quality->library = 'test_library';

		$result = $quality->invokeMethod('_syntaxFilters');
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
		$quality = $this->_quality();
		$rules = $this->_classes['rules'];

		$result = $quality->invokeMethod('_syntaxFilters');
		$this->assertTrue(is_array($result));

		$this->assertEqual(1, count($rules::$invocations));
		$call = $rules::$invocations[0];
		$this->assertEqual('ruleOptions', $call['method']);
	}

	protected function _quality(array $options = array()) {
		$request = $this->_request($options);
		$classes = $this->_classes;
		return new MockQuality(compact('request', 'classes'));
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