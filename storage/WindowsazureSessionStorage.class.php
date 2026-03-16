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
 * Provides support for session storage using a Windows Azure table store.
 *
 * Optional parameters:
 *
 * # host                    - The Azure table host to connect to.
 *                                    Defaults to local dev storage.
 * # account_name            - The account name to use for connecting.
 * # account_key             - The account key to use for connecting.
 * # session_table           - The name of the table to store to.
 *                                    Defaults to 'php-sessions'.
 * # session_table_partition - The table partition to store to.
 *                                    Defaults to 'sessions'.
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage storage
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class WindowsazureSessionStorage extends SessionStorage
{
	/**
	 * @var        Microsoft_WindowsAzure_SessionHandler Session handler object.
	 */
	protected $sessionHandler;

	/**
	 * Initialize this Storage.
	 *
	 * @param      Context An Context instance.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     Exceptions\Initialization If an error occurs while
	 *                                                 initializing this Storage.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com
	 */
	public function initialize(Context $context, array $parameters = array())
	{
		// initialize the parent
		parent::initialize($context, $parameters);
		
		if(!class_exists('Microsoft_WindowsAzure_SessionHandler')) {
			require_once('Microsoft/WindowsAzure/SessionHandler.php');
		}
		
		$table = new Microsoft_WindowsAzure_Storage_Table(
			$this->getParameter('host', Microsoft_WindowsAzure_Storage::URL_DEV_TABLE),
			$this->getParameter('account_name', Microsoft_WindowsAzure_Credentials::DEVSTORE_ACCOUNT),
			$this->getParameter('account_key', Microsoft_WindowsAzure_Credentials::DEVSTORE_KEY)
		);
		
		$sessionHandler = new Microsoft_WindowsAzure_SessionHandler($table, $this->getParameter('session_table', 'phpsessions'), $this->getParameter('session_table_partition', 'sessions'));
		// this will do session_set_save_handler
		$sessionHandler->register();
	}
}

?>
