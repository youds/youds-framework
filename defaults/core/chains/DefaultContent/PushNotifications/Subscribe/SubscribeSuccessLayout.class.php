<?php
namespace Defaults\Core\Chains\DefaultContent\PushNotifications\Subscribe;
use YoudsFramework\Request\DataHolder;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.						 |
// | Copyright (c) 2020 the Youds Framework Project.					  |
// |																		   |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code.						  |
// +---------------------------------------------------------------------------+

class Success extends BaseLayout
{

	public function executeJson($rd)
	{
		return json_encode(array('success' => true));
	}
}

?>
