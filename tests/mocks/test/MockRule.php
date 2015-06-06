<?php

namespace li3_quality\tests\mocks\test;

class MockRule extends \li3_quality\test\Rule {

	protected function _rule($rule, array $options = array()) {
		if (isset($this->rule)) {
			return $this->rule;
		}
		return parent::_rule($rule, $options);
	}

}

?>