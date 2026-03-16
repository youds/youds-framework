<?php
namespace YoudsFramework\Layout;
use YoudsFramework\Config;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Translation\Locale;
use YoudsFramework\Exceptions\Exception;

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
class StreamTemplateLayer extends TemplateLayer
{
	/**
	 * Constructor.
	 *
	 * @param      array Initial parameters.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 */
	public function __construct(array $parameters = array())
	{
		parent::__construct(array_merge(array(
			'check' => false,
			'scheme' => null,
			'targets' => array(
				'${template}',
			),
		), $parameters));
	}
	
	/**
	 * Get the full, resolved stream location name to the template resource.
	 *
	 * @return     string A PHP stream resource identifier.
	 *
	 * @throws     Exception If the template could not be found.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author	   Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getResourceStreamIdentifier()
	{
		$template = $this->getParameter('template');
		$template = substr($template, strrpos($template, '\\') ? strrpos($template, '\\') + 1 : 0);

		if($template === null) {
			// no template set, we return null so nothing gets rendered
			return null;
		}
		
		$args = array();
		if(Config::get('core.use_translation')) {
			// i18n is enabled, build a list of sprintf args with the locale identifier
			foreach(Locale::getLookupPath($this->context->getTranslationManager()->getCurrentLocaleIdentifier()) as $identifier) {
				$args[] = array('locale' => $identifier);
			}
		}
		
		if(empty($args)) {
			$args[] = array(); // add one empty arg to always trigger target lookups (even if i18n is disabled etc.)
		}
		
		$scheme = $this->getParameter('scheme');
		// TODO: a simple workaround for broken ubuntu and debian packages (fixed already), we can remove that for final 0.11
		if($scheme != 'file' && !in_array($scheme, stream_get_wrappers())) {
			throw new Exception('Unknown stream wrapper "' . $scheme . '", must be one of "' . implode('", "', stream_get_wrappers()) . '".');
		}
		$check = $this->getParameter('check');
		
		if (!isset($attempts))
			$attempts = array();
		// try each of the patterns
		foreach((array)$this->getParameter('targets', array()) as $pattern) {

			// try pattern with each argument list
			foreach($args as $arg) {
				$params = $this->getParameters();
				if (isset($params['template']))
					$params['template'] = substr($params['template'], strrpos($params['template'], '\\') ? strrpos($params['template'], '\\') + 1 : 0);

				$target = Toolkit::expandVariables($pattern, array_merge(array_filter($params, 'is_scalar'), array_filter($params, 'is_null'), $arg));

				// TODO (should they fix it): don't add file:// because suhosin's include whitelist is empty by default, does not contain 'file' as allowed uri scheme
				if($scheme != 'file') {
					$target = $scheme . '://' . $target;
				}

				// handle / in chain name - convert underscore-separated segments to nested paths
				$target = preg_replace_callback('/\/([^\/]+)\//', function ($matches) {
					return '/' . str_replace('_', '/', $matches[1]) . '/';
				}, $target);


				if(!$check || is_readable($target)) {
					return $target;
				}

				$attempts[] = $target;

				// now check for "hidden" content
				$params = $this->getParameters();

				if (!isset($params['chainName']) || strlen($params['chainName']) == 0):
                    $params['directory'] = sprintf('%s/core/chains', Config::get('core.defaults_dir'));
                else:
				    $params['directory'] = sprintf('%s/core/chains/%s/%s', Config::get('core.defaults_dir'), Toolkit::canonicalNameReverse($params['content']), Toolkit::canonicalNameReverse($params['chainName']));
                endif;

				$target = Toolkit::expandVariables($pattern, array_merge(array_filter($params, 'is_scalar'), array_filter($params, 'is_null'), $arg));

				// TODO (should they fix it): don't add file:// because suhosin's include whitelist is empty by default, does not contain 'file' as allowed uri scheme
				if($scheme != 'file') {
					$target = $scheme . '://' . $target;
				}		
				if(!$check || is_readable($target)) {
					return $target;
				}		
				$attempts[] = $target;
			}
		}

		// no template found time to throw an exception
		throw new Exception('Template "' . $template . '" could not be found. Paths tried:' . "\n" . implode("\n", $attempts));
	}
}

?>
