<?php
namespace Defaults\Core\Chains\DefaultContent\Static\Css;
use YoudsFramework\Request\DataHolder;
use \Config;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.						 |
// | Copyright (c) 2020 the Youds Framework Project.					  |
// |																		   |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code.						  |
// +---------------------------------------------------------------------------+

class Success extends BaseLayout
{

	public function executeCss($rd)
	{
		$this->setupHtml($rd);
		
		return 'Success';
	}
}

?>
