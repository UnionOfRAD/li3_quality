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

/**
 * Adds missing PHP file headers and updates existing.
 */
class Header extends \li3_quality\qa\Rule {

	public function enabled($testable, array $config = array()) {
		return $testable->isPHP() && file_exists($this->_template());
	}

	public function apply($testable, array $config = array()) {
		$contents = $testable->source();
		$contents = explode("\n", $contents);

		$template = Text::insert(file_get_contents($this->_template()), array(
			'year' => date('Y')
		));

		if ($contents[0] !== '<?php') {
			return;
		}
		$one = array_shift($contents);
		$header = explode("\n", $template);

		if ($contents[0] === '/**') {
			while($line = array_shift($contents)) {
				if ($line === ' */') {
					break;
				}
			}
		}

		$contents = array_merge($header, $contents);
		array_unshift($contents, $one);
		$testable->source(implode("\n", $contents));
	}

	protected function _template() {
		return Text::insert($config['template'], array(
			'cwd' => getcwd()
		));
	}
}

?>