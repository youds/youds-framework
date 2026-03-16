<?php
namespace YoudsFramework\Request\Environment;
use YoudsFramework\Context;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * Console provides support for console-only request information
 * such as command-line parameters.
 * 
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage request
 *
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Console extends Base implements RequestBase
{
	/**
	 * @var        string The command given on the command line (without parameters)
	 */
	protected $input = null;

	/**
	 * Constructor.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setParameters(array(
			'request_data_holder_class' => 'Request\Console',
		));
	}

	/**
	 * Initialize this Request.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this Request.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
     * @author     Craig Fairhurst <craig.fairhurst@youds.com>
     */
	public function initialize(Context $context, array $parameters = array())
	{
		parent::initialize($context, $parameters);
		
		$argv = self::getSourceValue('argv', array());
		// get rid of the script name
		array_shift($argv);
		
		$input = array();
		foreach($argv as $arg) {

			$input[] = $arg;
		}
		$files = array();
		if($this->getParameter('read_stdin', true) && defined('STDIN') && ($stdinMeta = stream_get_meta_data(STDIN)) && !$stdinMeta['seekable']) {
			// if stream_get_meta_data() reports STDIN as not seekable, that means something was piped into our process, and we should put that into a file
			// the alternative method to determine this is via posix_isatty(STDIN) which returns false in the same situation, but that requires the posix extension and also doesn't work on Windows
			$stdinName = $this->getParameter('stdin_file_name', 'stdin_file');
			
			$ufc = 'YoudsFramework\\Request\\' . $this->getParameter('uploaded_file_class', 'UploadedFile');
			$files = array(
				$stdinName => new $ufc(array(
					'name' => $stdinName,
					'type' => 'application/octet-stream',
					'size' => -1, // we're not buffering, so -1 is a good choice probably (better than 0 anyway)
					'stream' => STDIN,
					'error' => UPLOAD_ERR_OK,
					'is_uploaded_file' => false,
				))
			);
		}

		$rdhc = 'YoudsFramework\\' . $this->getParameter('request_data_holder_class');
		$this->setRequestData(new $rdhc($input));
		$rd = $this->getRequestData();

		foreach($parameters as $name => $value) {
			$rd->setParameter(substr($name, 1), $value);
		}

		$this->input = implode(' ', $input);
		
		$this->setMethod($this->getParameter('default_method', 'read'));
	}
	
	/**
	 * Get the command given on the command line (without parameters)
	 *
	 * @return     string The command.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Do any necessary startup work after initialization.
	 *
	 * This method is not called directly after initialize().
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function startup()
	{
		parent::startup();

		if($this->getParameter('unset_input', true)) {
			$_SERVER['argv'] = $_ENV['argv'] = $GLOBALS['argv'] = array();
			$_SERVER['argc'] = $_ENV['argc'] = $GLOBALS['argc'] = 0;
		}
	}
}

?>
