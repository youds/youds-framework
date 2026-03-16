<?php

namespace YoudsFramework;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Version initialization script.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */

Config::set('framework.name', 'Youds Framework');

Config::set('framework.major_version', '0');
Config::set('framework.minor_version', '1');
Config::set('framework.micro_version', '0');
//Config::set('framework.status', 'dev');
Config::set('framework.branch', 'trunk');

Config::set('framework.version',
	Config::get('framework.major_version') . '.' .
	Config::get('framework.minor_version') . '.' .
	Config::get('framework.micro_version') .
	(Config::has('framework.status')
		? '-' . Config::get('framework.status')
		: '')
);

Config::set('framework.release',
	Config::get('framework.name') . '/' .
	Config::get('framework.version')
);

Config::set('framework.url', 'https://framework.youds.com');

Config::set('framework_info', sprintf('<a href="%s">%s v%s</a>', Config::get('framework.url'), Config::get('framework.name'), Config::get('framework.version')));

?>
