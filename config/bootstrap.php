<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\Libraries;
use lithium\g11n\Multibyte;

Libraries::paths(array(
	'rules' => array(
		'{:library}\extensions\test\rules\{:class}\{:name}',
		'{:library}\test\rules\{:class}\{:name}' => array('libraries' => 'li3_quality'),
	),
	'ruleSets' => array(
		'{:library}\test\*{rules,defaultRules}',
	),
));

if (!Multibyte::config('li3_quality')) {
	Multibyte::config(array('li3_quality' => array('adapter' => 'Mbstring')));
}

?>