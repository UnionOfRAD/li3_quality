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
		'{:library}\extensions\qa\rules\{:class}\{:name}',
		'{:library}\qa\rules\{:class}\{:name}' => array('libraries' => 'li3_quality')
	)
));

Multibyte::config(array('li3_quality' => array('adapter' => 'Mbstring')));

?>