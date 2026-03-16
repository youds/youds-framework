<?php
namespace YoudsFramework\Layout;
use YoudsFramework\Config;
use YoudsFramework\Context;
use YoudsFramework\Util\Toolkit;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Template layer implementation for templates fetched using a PHP stream.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage layout
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class FileTemplateLayer extends StreamTemplateLayer
{
	/**
	 * Constructor.
	 *
	 * @param		array Initial parameters.
	 *
	 * 	David Zülke <dz@bitxtender.com>
	 * 	Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function __construct(array $parameters = array())
	{
		$targets = array();
		if(Config::get('core.use_translation')) {
			$targets[] = '${directory}/${template}${extension}';
			$targets[] = '${directory}/${template}.${locale}${extension}';
		}
		$targets[] = '${directory}/${template}${extension}';
		
		parent::__construct(array_merge(array(
			'directory' => Config::get('core.core_dir') . '/${content}',
			'scheme' => 'file',
			'check' => true,
			'targets' => $targets,
		), $parameters));
		
	}
	
	/**
	 * Initialize the layer.
	 *
	 * Will try and figure out an alternative default for "directory".
	 *
	 * @param      Context The current Context instance.
	 * @param      array        An array of initialization parameters.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		$this->setParameter('directory', Toolkit::evaluateModuleDirective($parameters['content'], 'framework.template.directory'));
		parent::initialize($context, $parameters);
	}
	
	/**
	 * Get the full, resolved stream location name to the template resource.
	 *
	 * @return     string A PHP stream resource identifier.
	 *
	 * @throws     Exception If the template could not be found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author    Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getResourceStreamIdentifier()
	{
		$retVal = null;

		$template = $this->getParameter('template') . 'Template';

		/* TODO: Getting chain from string, seems wrong. */
		preg_match_all('/[A-Z]/', $this->getParameter('template'), $matches, PREG_OFFSET_CAPTURE);
		$chain = Toolkit::canonicalName(substr($template, 0, $matches[0][count($matches[0]) - 1][1]));


		$this->setParameter('content', Toolkit::canonicalNameReverse($this->getParameter('content')));
		$this->setParameter('chainName', Toolkit::canonicalNameReverse($chain));
		
		if($template === null) {
			// no template set, we return null so nothing gets rendered
			return null;
		} elseif(Toolkit::isPathAbsolute($template)) {
			// the template is an absolute path, ignore the dir
			$directory = dirname($template);
			$template = basename($template);
		} else {
			$directory = $this->getParameter('directory');
		}
		// treat the directory as sprintf format string and inject Core Content Directive
		$directory = Toolkit::expandVariables($directory, array_merge(array_filter($this->getParameters(), 'is_scalar'), array_filter($this->getParameters(), 'is_null')));

		$this->setParameter('directory', $directory);
		$this->setParameter('template', $template);
		if(!$this->hasParameter('extension')) {
			$this->setParameter('extension', $this->renderer->getDefaultExtension());
		}
		
		// everything set up for the parent
		return parent::getResourceStreamIdentifier();
	}
}

?>
