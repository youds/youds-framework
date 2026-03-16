<?php
namespace YoudsFramework\Renderer;
use YoudsFramework\Renderer;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * A renderer produces the output as defined by a Layout
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage renderer
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @author     Benjamin Muskalla <bm@bmuskalla.de>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Phptal extends Renderer implements IRenderer
{
	/**
	 * @constant   string The directory inside the cache dir where templates will
	 *                    be stored in compiled form.
	 */
	const COMPILE_DIR = 'templates';
	
	/**
	 * @constant   string The subdirectory inside the compile dir where templates
	 *                    will be stored in compiled form.
	 */
	const COMPILE_SUBDIR = 'phptal';
	
	/**
	 * @var        string A string with the default template file extension,
	 *                    including the dot.
	 */
	protected $defaultExtension = '.tal';

	/**
	 * @var        PHPTAL PHPTAL template engine.
	 */
	protected $phptal = null;

	/**
	 * Pre-serialization callback.
	 *
	 * Excludes the PHPTAL instance to prevent excessive serialization load.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __sleep()
	{
		$keys = parent::__sleep();
		unset($keys[array_search('phptal', $keys)]);
		return $keys;
	}
	
	/**
	 * Create an instance of PHPTAL and initialize it correctly.
	 *
	 * @return     PHPTAL The PHPTAL instance.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	protected function createEngineInstance()
	{
		$phptalPhpCodeDestination = Config::get('core.cache_dir') . DIRECTORY_SEPARATOR . PhptalRenderer::COMPILE_DIR . DIRECTORY_SEPARATOR . PhptalRenderer::COMPILE_SUBDIR . DIRECTORY_SEPARATOR;
		
		// we keep this for < 1.2
		if(!defined('PHPTAL_PHP_CODE_DESTINATION')) {
			define('PHPTAL_PHP_CODE_DESTINATION', $phptalPhpCodeDestination);
		}
		
		Toolkit::mkdir($phptalPhpCodeDestination, fileperms(Config::get('core.cache_dir')), true);
		
		if(!class_exists('PHPTAL')) {
			require_once('PHPTAL.php');
		}
		
		$phptal = new PHPTAL();
		
		if(version_compare(PHPTAL_VERSION, '1.2', 'ge')) {
			$phptal->setPhpCodeDestination($phptalPhpCodeDestination);
		} else {
		  trigger_error('Support for PHPTAL versions older than 1.2 is deprecated and may be removed in a later version of Youds Framework.', E_USER_DEPRECATED);
		}
		
		if($this->hasParameter('encoding')) {
			$phptal->setEncoding($this->getParameter('encoding'));
		}
		
		return $phptal;
	}

	/**
	 * Retrieve the PHPTAL instance
	 *
	 * @return     PHPTAL A PHPTAL instance.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Benjamin Muskalla <bm@muskalla.de>
	 */
	protected function getEngine()
	{
		if($this->phptal) {
			return $this->phptal;
		}
		
		$this->phptal = $this->createEngineInstance();
		
		return $this->phptal;
	}

	/**
	 * Render the presentation and return the result.
	 *
	 * @param      TemplateLayer The template layer to render.
	 * @param      array              The template variables.
	 * @param      array              The slots.
	 * @param      array              Associative array of additional assigns.
	 *
	 * @return     string A rendered result.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Benjamin Muskalla <bm@bmuskalla.de>
	 */
	public function render(TemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
	{
		$engine = $this->getEngine();
		
		if($this->extractVars) {
			foreach($attributes as $key => $value) {
				$engine->set($key, $value);
			}
		} else {
			$engine->set($this->varName, $attributes);
		}
		
		$engine->set($this->slotsVarName, $slots);
		
		foreach($this->assigns as $key => $getter) {
			$engine->set($key, $this->context->$getter());
		}
		
		$finalMoreAssigns =& self::buildMoreAssigns($moreAssigns, $this->moreAssignNames);
		foreach($finalMoreAssigns as $key => &$value) {
			$engine->set($key, $value);
		}
		
		$engine->setTemplate($layer->getResourceStreamIdentifier());
		
		return $engine->execute();
	}
}

?>
