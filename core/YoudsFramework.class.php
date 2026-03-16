<?php
namespace YoudsFramework;
use YoudsFramework\Config\Cache;
use YoudsFramework\Util\Toolkit;
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
 * Base framework class used for autoloading and initial bootstrapping of .
 *
 *
 * @author     David Zülke <dz@bitxtender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
final class YoudsFramework
{
	
	/**
	 * Startup the Youds Framework core
	 *
	 * @param      string environment the environment to use for this session.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public static function bootstrap($environment = null)
	{
		// set up our __autoload
		spl_autoload_register(array('YoudsFramework\Util\Autoloader', 'loadClass'));

		try {
			if ($environment === null) {
				
				// no env given? let's read one from core.environment
				$environment = Config::get('core.environment');
			} elseif(Config::has('core.environment') && Config::isReadonly('core.environment')) {
				// env given, but core.environment is read-only? then we must use that instead and ignore the given setting
				$environment = Config::get('core.environment');
			}
			
			if($environment === null) {
				// still no env? oh man...
				throw new Exception('You must supply an environment name to ::bootstrap() or set the name of the default environment to be used in the configuration directive "core.environment".');
			}
			
			// finally set the env to what we're really using now.
			Config::set('core.environment', $environment, true, true);

			Config::set('core.debug', false, false);

			if(!Config::has('core.root_dir') || Config::get('core.root_dir') == '') {
				throw new Exception('Configuration directive "core.root_dir" not defined, terminating...');
			}
			
			// define a few filesystem paths
			Config::set('core.app_dir', Config::get('core.root_dir') . '/app', true, true);

            Config::set('core.cache_dir', Config::get('core.root_dir') . '/storage/cache', true, true);
			
			Config::set('core.core_dir', Config::get('core.root_dir') . '/core', true, true);
			
			Config::set('core.common_dir', Config::get('core.core_dir') . '/common', true, true);

			Config::set('core.generator_dir', Config::get('core.common_dir') . '/generator', true, true);

            Config::set('core.assets_dir', Config::get('core.core_dir') . '/assets', true, true);

            Config::set('core.config_dir', Config::get('core.root_dir') . '/config', true, true);

			Config::set('core.framework_config_dir', Config::get('core.src_dir') . '/config/defaults', true, true);

			Config::set('core.base_dir', Config::get('core.common_dir') . '/base', true, true);

			Config::set('core.model_dir', Config::get('core.core_dir') . '/models', true, true);

			Config::set('core.chains_dir', Config::get('core.core_dir') . '/chains', true, true);

			Config::set('core.output_dir', Config::get('core.core_dir') . '/Chains', true, true);
			
			Config::set('core.testing_dir', Config::get('core.core_dir') . '/tests', true, true);
			
			Config::set('core.composer_dir', Config::get('core.root_dir') . '/vendor', true, true);

			Config::set('core.cldr_dir', Config::get('core.src_dir') . '/translation/data', true, true);
			
			Config::set('core.defaults_dir', Config::get('core.src_dir') . '/defaults', true, true);

			Config::set('core.defaults_model_dir', Config::get('core.defaults_dir') . '/Core/Models', true, true);

			Config::set('core.defaults_generator_dir', Config::get('core.defaults_dir') . '/core/common/generator', true, true);

			Config::set('core.defaults_testing_dir', Config::get('core.defaults_dir') . '/core/tests', true, true);

			Config::set('core.migrations_dir', Config::get('core.app_dir') . '/migrations', true, true);
			
			Config::set('core.storage_dir', Config::get('core.root_dir') . '/storage', true, true);
			
			// load base settings
			Cache::load(Config::get('core.config_dir') . '/settings.xml');
			
			// load integrations
			Cache::load(Config::get('core.config_dir') . '/integrations.xml');
			
			// clear our cache if the conditions are right
			if(Config::get('core.debug')) {
				Toolkit::clearCache();

				// load base settings
				Cache::load(Config::get('core.config_dir') . '/settings.xml');
			}
			
			// composer
			if (is_readable(Config::get('core.composer_dir') . '/autoload.php'))
				require_once(Config::get('core.composer_dir') . '/autoload.php');
			
			// repodeps
			if (is_readable(Config::get('core.root_dir') . '/repodeps.php'))
				require_once(Config::get('core.root_dir') . '/repodeps.php');

            // ban the bots support
            if (!str_contains(Config::get('core.environment'), 'dev') && class_exists('BanTheBots')):
                global $banTheBots;
                $banTheBots = new \BanTheBots(['baseDir' => str_replace(basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_FILENAME'])]);
                $banTheBots->apply();
            endif;

			// callbacks
			foreach (glob(Config::get('core.common_dir') . '/callbacks/*/*.php') as $filename)
			{
			    include_once($filename);
			}

        } catch(Exception $e) {
			Exception::render($e);
		}
	}
	
}

?>
