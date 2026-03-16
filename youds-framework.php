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
 * Pre-initialization script.
 *
 * @package    Youds Framework - https://framework.youds.com
 *
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Vincent <mike@agavi.org>
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */

// load the Config class
require_once(__DIR__ . '/config/Config.class.php');

// check minimum PHP version
Config::set('core.minimum_php_version', '5.3');
if(version_compare(PHP_VERSION, Config::get('core.minimum_php_version'), '<') ) {
	trigger_error('Youds Framework requires PHP version ' . Config::get('core.minimum_php_version') . ' or greater', E_USER_ERROR);
}

// define a few filesystem paths
Config::set('core.src_dir', $framework_config_directive_core_dir = __DIR__, true, true);

// default exception template
Config::set('exception.default_template', $framework_config_directive_core_dir . '/exceptions/templates/shiny.php');

// required files
require_once($framework_config_directive_core_dir . '/version.php');
include(__DIR__ . '/dump.php');
include(__DIR__ . '/developer-bar.php');

require_once($framework_config_directive_core_dir . '/core/YoudsFramework.class.php');
require_once($framework_config_directive_core_dir . '/util/Autoloader.class.php');
// required files for classes Youds Framework and Cache to run
// consider this the bare minimum we need for bootstrapping
require_once($framework_config_directive_core_dir . '/util/Inflector.class.php');
require_once($framework_config_directive_core_dir . '/util/ArrayPathDefinition.class.php');
require_once($framework_config_directive_core_dir . '/util/VirtualArrayPath.class.php');
require_once($framework_config_directive_core_dir . '/request/ParameterHolder.class.php');
require_once($framework_config_directive_core_dir . '/config/Cache.class.php');
require_once($framework_config_directive_core_dir . '/exceptions/Exception.class.php');
require_once($framework_config_directive_core_dir . '/exceptions/Autoload.class.php');
require_once($framework_config_directive_core_dir . '/exceptions/Cache.class.php');
require_once($framework_config_directive_core_dir . '/exceptions/Configuration.class.php');
require_once($framework_config_directive_core_dir . '/exceptions/Unreadable.class.php');
require_once($framework_config_directive_core_dir . '/exceptions/Parse.class.php');
require_once($framework_config_directive_core_dir . '/util/Toolkit.class.php');

//include_once(__DIR__ . '/errorHandling.php');


// clean up (we don't want collisions with whatever file included us, in case you were wondering about the ugly name of that var)
unset($framework_config_directive_core_dir);

?>
