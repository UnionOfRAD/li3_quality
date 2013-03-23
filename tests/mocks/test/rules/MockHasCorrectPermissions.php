<?php

namespace li3_quality\tests\mocks\test\rules;

use li3_quality\test\rules\HasCorrectPermissions;

/**
 * A mock of the Rule object.
 */
class MockHasCorrectPermissions extends HasCorrectPermissions {

	protected function _isExecutable($path) {
		return $this->_config['executeable'];
	}

}

?>