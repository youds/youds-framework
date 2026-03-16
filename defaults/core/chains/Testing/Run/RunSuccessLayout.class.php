<?php
namespace Defaults\Core\Chains\Testing\Run;
use Defaults\Core\Common\Base\Layout as Project;
use YoudsFramework\Request\DataHolder;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.						   |
// | Copyright (c) 2020 the Youds Framework Project.					       |
// |																		   |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code.						   |
// +---------------------------------------------------------------------------+

class Success extends Project
{
	public function executeHtml ($rd) {
		
		$this->setupHtml($rd);
		return 'Success';
	}
	public function executeText () {
		
		return 'testing:run success';
	}
}

?>
