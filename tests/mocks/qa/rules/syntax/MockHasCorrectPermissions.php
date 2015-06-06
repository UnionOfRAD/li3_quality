<?php

namespace li3_quality\tests\mocks\qa\rules\syntax;

use li3_quality\qa\rules\syntax\HasCorrectPermissions;

/**
 * A mock of the Rule object
 */
class MockHasCorrectPermissions extends HasCorrectPermissions {

	protected function _isExecutable($path) {
		return $this->_config['executeable'];
	}

}

?>