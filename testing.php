<?php
namespace YoudsFramework;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Migrations\Migration;
use YoudsFramework\Config\Cache;


// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * bootstrap file for the Testing
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage testing
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
$root = realpath(__DIR__);

// when the composer autoload was found Youds Framework will already be loaded
// load Youds Framework basics
require_once($root . '/youds-framework.php');
Config::set('core.default_context', 'testing');
require_once($root . '/testing/Testing.class.php');
require_once($root . '/testing/PhpUnitCli.class.php');

?>
