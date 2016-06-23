<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2015, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\qa\rules\fix;

use lithium\core\Libraries;
use lithium\util\Text;

class Header extends \li3_quality\qa\Rule {

	public function enabled($testable, array $config = array()) {
		return file_exists(Text::insert($config['template'], array(
			'library' => Libraries::get(true, 'path')
		)));
	}

	public function apply($testable, array $config = array()) {
		$contents = $testable->source();
		$contents = explode("\n", $contents);

		$template = Text::insert($config['template'], array(
			'library' => Libraries::get(true, 'path')
		));
		$template = Text::insert(file_get_contents($template), array(
			'year' => date('Y')
		));

		if (strpos($contents[1], '*') === false) {
			$header = explode("\n", $template);

			$one = array_shift($contents);
			$contents = array_merge($header, $contents);
			array_unshift($contents, $one);

			$testable->source(implode("\n", $contents));
		}
	}
}

?>