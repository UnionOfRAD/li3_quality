<?php

namespace li3_quality\tests\mocks\test\rules\syntax;

use li3_quality\test\rules\syntax\HasCorrectPermissions;

/**
 * A mock of the Rule object
 */
class MockHasCorrectPermissions extends HasCorrectPermissions {

	protected function _isExecutable($path) {
		return $this->_config['executeable'];
	}

}

?>