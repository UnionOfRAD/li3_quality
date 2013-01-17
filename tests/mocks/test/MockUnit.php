<?php

namespace li3_quality\tests\mocks\test;

class MockUnit extends \li3_quality\test\Unit {

	protected function _rule($rule, array $options = array()) {
		if (isset($this->rule)) {
			return $this->rule;
		}
		return parent::_rule($rule, $options);
	}

}

?>