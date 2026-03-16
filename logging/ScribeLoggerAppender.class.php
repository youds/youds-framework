<?php
namespace YoudsFramework\Logging;

// +---------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         |
// | Copyright (c) 2022 the Youds Framework Project.                      |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE |
// | file that was distributed with this source code. You can also view the  |
// | LICENSE file online at http://framework.youds.com/download/LICENSE        |
// +---------------------------------------------------------------------------+

/**
 * ScribeLoggerAppender sends Messages to a Scribe server or an
 * interface using the Scribe Thrift protocol (Facebook Scribe, Cloudera Flume).
 *
 * Configuration parameters:
 *  'buffer'                 - Whether or not to buffer all messages and only
 *                             send them on shutdown. Defaults to false.
 *  'default_category'       - Default scribe category for messages ("default"),
 *                             can be overriden in a message using parameter
 *                             "scribe_category".
 *  'socket_host'            - Hostname of scribe server (default "localhost")
 *  'socket_port'            - Port of scribe server (default 1463)
 *  'socket_persist'         - Whether to use persistent conns (default false)
 *  'socket_timeout'         - Socket timeout in seconds (default The thrift default)
 *  'transport_strict_read'  - Strict protocol reads (default false)
 *  'transport_strict_write' - Strict protocol writes (default true)
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage logging
 *
 * @author     David Zülke <david.zuelke@bitextender.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class ScribeLoggerAppender extends Appender
{
	/**
	 * @var        scribeClient The scribeClient instance to write to.
	 */
	protected $scribeClient = null;
	
	/**
	 * @var        TTransport The Thrift transport instance to use.
	 */
	protected $transport = null;
	
	/**
	 * @var        array A buffer of messages to log
	 */
	protected $buffer = array();
	
	/**
	 * Retrieve the scribeClient instance to write to.
	 *
	 * @return     scribeClient The scribeClient instance to write to.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	protected function getScribeClient()
	{
		if(!$this->scribeClient) {
			$socketClass = $this->getParameter('socket_class', 'TSocket');
			$socket = new $socketClass($this->getParameter('socket_host', 'localhost'), $this->getParameter('socket_port', 1463), $this->getParameter('socket_persist', false));
			if($this->hasParameter('socket_timeout')) {
				// setRecvTimeout takes milliseconds
				$socket->setRecvTimeout(1000 * $this->getParameter('socket_timeout'));
			}
			
			$transportClass = $this->getParameter('transport_class', 'TFramedTransport');
			$this->transport = new $transportClass($socket);
			
			$protocolClass = $this->getParameter('protocol_class', 'TBinaryProtocol');
			$protocol = new $protocolClass($this->transport, $this->getParameter('transport_strict_read', false), $this->getParameter('transport_strict_write', true));
			
			$clientClass = $this->getParameter('client_class', 'scribeClient');
			$this->scribeClient = new $clientClass($protocol, $protocol);
			
			try {
				$this->transport->open();
			} catch(Exceptions\T $e) {
				$this->scribeClient = null;
				$this->transport = null;
				throw new Logging(sprintf("Failed to connect to Scribe server:\n\n%s", $e->getMessage()), 0, $e);
			}
		}
		
		return $this->scribeClient;
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * Tells the Scribe client to flush and send a shutdown command.
	 * Underlying sockets will be auto-closed at the end of the script.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function shutdown()
	{
		try {
			$this->flush();
		} catch(Exceptions\Logging $e) {
			// not much we can do at this point...
		}
		if($this->transport) {
			$this->transport->close();
		}
	}

	/**
	 * Write log data to this appender.
	 *
	 * @param      Message Log data to be written.
	 *
	 * @throws     Exceptions\Logging if no Layout is set or the stream
	 *                                          cannot be written.
	 *
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	public function write(Message $message)
	{
		if(($layout = $this->getLayout()) === null) {
			throw new Logging('No Layout set');
		}
		
		$this->buffer[] = new LogEntry(array(
			'category' => $message->getParameter('scribe.category', $this->getParameter('default_category', 'default')),
			'message' => (string)$this->getLayout()->format($message),
		));
		
		if(!$this->getParameter('buffer', false)) {
			$this->flush();
		}
	}
	
	/**
	 * Send buffer contents if there are any.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 */
	protected function flush()
	{
		if(!$this->buffer) {
			// nothing to send
			return;
		}
		
		$this->getScribeClient()->Log($this->buffer);
		
		$this->buffer = array();
	}
}

?>
