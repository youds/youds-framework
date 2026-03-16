<?php
namespace YoudsFramework\Exceptions;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * Exceptions\DisabledModule is thrown when Controller::initializeContent
 * gets called on a disabled content
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage exception
 *
 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class DisabledModule extends Initialization
{
}

?>
