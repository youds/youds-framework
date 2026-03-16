<?php
namespace YoudsFramework\Testing;
use PHPUnit\TextUI\Command as PhpUnitCommand;
use PHPUnit\Util\Test as TestUtil;
use YoudsFramework\Util\Toolkit;
use YoudsFramework\Config\Cache;
use YoudsFramework\Config;
use YoudsFramework\Exceptions\Exception;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2005-2014 the Youds Framework Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+


/**
 * Main framework class used for running tests on the command line interface.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage testing
 *
 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 */
class PhpUnitCli extends PhpUnitCommand
{
	
	/**
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function __construct()
	{
		$this->longOptions['environment='] = 'handleEnvironment';
		$this->longOptions['include-suite='] = 'handleIncludeSuite';
		$this->longOptions['exclude-suite='] = 'handleExcludeSuite';
		$this->longOptions['no-expand-configuration'] = 'handleNoExpandConfiguration';
		
		$this->arguments['environment'] = !empty($_SERVER['YOUDS_FRAMEWORK_ENVIRONMENT']) ? $_SERVER['YOUDS_FRAMEWORK_ENVIRONMENT'] : 'testing';
		$this->arguments['includeSuites'] = array();
		$this->arguments['excludeSuites'] = array();
		$this->arguments['expandConfiguration'] = true;
	}

	/**
	 * Callback handling the --environment command line option.
	 *
	 * @param      string The Youds Framework environment name.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	protected function handleEnvironment($value)
	{
		$this->arguments['environment'] = $value;
	}
	
	/**
	 * Callback handling the --include-suite command line option.
	 *
	 * @param      string The suite names, separated by comma, to include.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	protected function handleIncludeSuite($value)
	{
		if (isset($this->arguments['includeSuites']) && is_array($this->arguments['includeSuites'])):	
			$this->arguments['includeSuites'] = array_merge(
				$this->arguments['includeSuites'],
				explode(',', $value)
			);
		else:
			$this->arguments['includeSuites'] = (is_array($value)?$value:explode(',', $value));
		endif;
	}
	
	/**
	 * Callback handling the --exclude-suite command line option.
	 *
	 * @param      string The suite names, separated by comma, to exclude.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	protected function handleExcludeSuite($value)
	{
		if (isset($this->arguments['excludeSuites']) && is_array($this->arguments['excludeSuites'])):	
			$this->arguments['excludeSuites'] = array_merge(
				$this->arguments['excludeSuites'],
				explode(',', $value)
			);
		else:
			$this->arguments['excludeSuites'] = (is_array($value)?$value:explode(',', $value));
		endif;
	}
	
	/**
	 * Callback handling the --no-expand-configuration command line option.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	protected function handleNoExpandConfiguration()
	{
		$this->arguments['expandConfiguration'] = false;
	}
	
	
	/**
	 * Dispatch the test run.
	 *
	 * @param      array An array containing the command line arguments
	 * @param      bool  Whether exit() should be called with an appropriate shell
	 *                   exit status to indicate success or failures/errors.
	 *
	 * @return     int   The return process return code (if $exit was false)
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public static function dispatch($argv, $exit = true) {

        $command = new static();

        if (method_exists(\PHPUnit\TextUI\CliArguments\Configuration::class, 'hasGenerateConfiguration')) {
            return $command->run($argv, $exit);
        } else {
            // Provide a fallback or a compatible approach here
            throw new Exception(
                'The current version of PHPUnit does not support the method "hasGenerateConfiguration". ' .
                'Ensure that your custom intcdegration matches the installed PHPUnit version.'
            );
        }

	}
	
	/**
	 * Show the help message.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public function showHelp() :void
	{
		parent::showHelp();
		echo <<<EOT

Youds Framework specific arguments:

  --environment <envname>   use environment named <envname> to run the tests.
                            Defaults to "testing".
  --include-suite <suites>  run only suites named <suite>, accepts a list of
                            suites, comma separated.
  --exclude-suite <suites>  run all but suites named <suite>, accepts a list
                            of suites, comma separated.
  --no-expand-configuration Don't expand configuration variables in the 
                            configuration file
 
NOTE:
  Unless --no-expand-configuration is given the configuration file given to
  PHPUnit is generated in YoudsFramework's cache directory. So you can't use relative
  paths in the configuration file. Use %framework.app_dir%, %core.testing_dir% or
  something applicable to your case.


EOT;
	}

	/**
	 * Custom callback for test suite discovery.
	 * This is called by PHPUnit in the setup process, right after all command line 
	 * arguments have been parsed.
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	protected function handleCustomTestSuite() :void
	{
		// ensure the bootstrap script doesn't run and bootstraps another time
		define('YOUDS_FRAMEWORK_TESTING_BOOTSTRAPPED', true);
		Toolkit::clearCache();
		$this->bootstrap('console');
		

		$directory = Config::get('testing.directory');
		$suites = Config::get('testing.suites.' . Config::get('testing.suites.default') . '.*');

		$masterSuite = new TestSuite('Master');
		foreach ($suites as $suite):

			foreach (glob($suite) as $filename):

				// get methods in file
				require($filename);
				$tests = str_replace(Config::get('core.testing_dir') . '/', '', str_replace('.php', '', $filename));
				$tests = str_replace(Config::get('core.defaults_testing_dir') . '/', '', str_replace('.php', '', $tests));

                $t = explode('\\', $tests);
                foreach ($t as $k => $v):
                    $t[$k] = ucfirst($v);
                endforeach;
                $tests = implode('\\', $t);
				$base = substr($tests, 0, strrpos($tests, '/'));
				$class = 'Core\Tests\\' . Toolkit::canonicalName(ucwords($tests));
				if (!class_exists($class))
					$class = 'Defaults\Core\Tests\\' . Toolkit::canonicalName(ucwords($tests));

				if (class_exists($class))
					$object = new $class();
				else
					throw new Exception(sprintf('Test "%s" could not be found', $tests));

				$methodNames = preg_grep('/^test/', get_class_methods($object));
		
				// loop methods
				foreach ($methodNames as $methodName):
				
					// check for match with cli arguments
					if (isset($this->arguments['includeSuites']) && !in_array($methodName, $this->arguments['includeSuites']))
						continue;
					if (isset($this->arguments['excludeSuites']) && in_array($methodName, $this->arguments['excludeSuites']))
						continue;
				
					// dataProvider
					$annotations = TestUtil::parseTestMethodAnnotations(
			        	$class,
			        	$methodName
			        );
					$argv = NULL;
					$argvMethod = NULL;
					if (isset($annotations['method']['dataProvider'][0])):
						$dataProvider = $annotations['method']['dataProvider'][0];
						$classObject = new $class();
						$argv = $classObject->$dataProvider();
						$argvMethod = $dataProvider;
					endif;
					if (!is_array($argv) || count($argv) == count($argv, COUNT_RECURSIVE)):
						$argv = array($argv);
					endif;
				
					foreach ($argv as $key => $value):
						$masterSuite->addTest(
							self::createSuite(
								$methodName, 
								array(
									'base' => $base, 
									'class' => $class,
									'argv' => $value,
									'argvMethod' => $argvMethod
								)
							)
						);
					endforeach;
				endforeach;
			endforeach;
		endforeach;
		/*		
		if(isset($this->arguments['includeSuites']) && $this->arguments['includeSuites'] != NULL) {
			foreach($this->arguments['includeSuites'] as $name) {
				if(empty($suites[$name])) {
					throw new InvalidArgument(sprintf('Invalid suite name %1$s.', $name));
				}
				
				$masterSuite->addTest(self::createSuite($name, $suites[$name]));
			}
		} else {
			foreach($suites as $name => $suite) {
				if(!in_array($name, $this->arguments['excludeSuites'])) {
					$masterSuite->addTest(self::createSuite($name, $suite));
				}
			}
		}
		*/
		$this->arguments['test'] = $masterSuite;
	}
	
	/**
	 * Initialize a suite from the given instructions and add registered tests.
	 *
	 * @param      string Name of the suite
	 * @param      array  An array containing information about the suite
	 *
	 * @return     TestSuite The initialized test suite object.
	 *
	 * @author     Felix Gilcher <felix.gilcher@bitextender.com>
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	protected static function createSuite($name, array $suite)
	{
		$base = (null == $suite['base']) ? 'tests' : $suite['base'];
		if(!Toolkit::isPathAbsolute($base)) {
			$base = Config::get('core.testing_dir') . '/' . $base;
		}
		$s = new $suite['class']($name, (is_array($suite['argv'])?$suite['argv']:array()), $suite['argvMethod']);
		
		if(!empty($suite['includes'])) {
			$files = iterator_to_array(new RecursiveIteratorIterator(
				new RecursiveDirectoryFilterIterator(
					new RecursiveDirectoryIterator($base),
					$suite['includes'],
					$suite['excludes']
				),
				RecursiveIteratorIterator::CHILD_FIRST
			));
			
			// ensure that the execution order of the tests is always in deterministic
			// order and doesn't depend on the filesystem order
			usort($files, function($a, $b) {
				return strcmp($a->getPathName(), $b->getPathName());
			});
			
			foreach($files as $finfo) {
				if($finfo->isFile()) {
					$s->addTestFile($finfo->getPathName());
				}
			}
		}
		if (isset($suite['testfiles'])):
			foreach($suite['testfiles'] as $file) {
				if(!Toolkit::isPathAbsolute($file)) {
					$file = $base . '/' . $file;
				}
				$s->addTestFile($file);
			}
		endif;
		return $s;
	}
	
	/**
	 * Runs Toolkit::expandDirectives() on all attributes and text nodes of
	 * the given file and writes a it to a new file in the Youds Framework cache directory.
	 *
	 * @param      string The path to the xml file
	 * 
	 * @return     string The path to the expanded file
	 *
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	private static function expandConfiguration($file) {
		// file does not exist, let PHPUnit handle that case
		if(!is_readable($file) || !is_file($file)) {
			return $file;
		}
		
		$doc = new \DOMDocument();
		$doc->substituteEntities = true;
		$doc->load($file);
		$xpath = new \DOMXPath($doc);
		$attributeNodes = $xpath->query('//@*');
		foreach($attributeNodes as $attributeNode) {
			$attributeNode->value = Toolkit::expandDirectives($attributeNode->value);
		}
		$textNodes = $xpath->query('//text()');
		foreach($textNodes as $textNode) {
			$textNode->nodeValue = Toolkit::expandDirectives($textNode->nodeValue);
		}
		
		$translatedFile = Cache::getCacheName($file);
		Cache::writeCacheFile($file, $translatedFile, $doc->saveXML());
		return $translatedFile;
	}
	
	/**
	 * Startup the Youds Framework core
	 *
	 * @param      string environment the environment to use for this session.
	 *
	 * @author     Felix Gilcher <felix.gilcher@exozet.com>
	 * @author     Dominik del Bondio <dominik.del.bondio@bitextender.com>
	 */
	public static function bootstrap($environment = null)
	{
		if($environment === null) {
			// no env given? let's read one from testing.environment
			$environment = Config::get('testing.environment');
		} elseif(Config::has('testing.environment') && Config::isReadonly('testing.environment')) {
			// env given, but testing.environment is read-only? then we must use that instead and ignore the given setting
			$environment = Config::get('testing.environment');
		}
		
		if($environment === null) {
			// still no env? oh man...
			throw new Exception('You must supply an environment name to Testing::bootstrap() or set the name of the default environment to be used for testing in the configuration directive "testing.environment".');
		}
		
		// finally set the env to what we're really using now.
		Config::set('testing.environment', $environment, true, true);

		
		// bootstrap the framework for autoload, config handlers etc.
		\YoudsFramework\YoudsFramework::bootstrap($environment);
		
		ini_set('include_path', get_include_path() . PATH_SEPARATOR . dirname(__DIR__));
		
		$GLOBALS['YOUDS_FRAMEWORK_CONFIG'] = Config::toArray();
	}
}

?>
