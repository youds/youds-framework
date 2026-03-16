<?php
namespace YoudsFramework\WebSockets;
use YoudsFramework\WebSockets\Server as WebSocketsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\SecureServer;
use React\Socket\Server as ReactServer;
use React\EventLoop\Factory as LoopFactory;

/**
 * WebSocketsManager provides access to WebSockets facilities.
 *
 * @package	Youds Framework - https://framework.youds.com
 * @subpackage generator
 *
 * @author	 Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since	  0.1
 *
 * @version	$Id$
 */
class Manager {

	
	/**
	 * Get active WebSockets Server state
	 *
	 * @return Ratchet server instance
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function getServer()
	{
		
		return new WebSocketsServer();
		
	}
	
	/**
	 * Start a functioning instance of a Web Sockets Server
	 *
	 * @return void
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function startServer (WebSocketsServer $ws, $port = 8043)
	{
		// Create an event loop
		$loop = LoopFactory::create();

		// Create a TCP socket server
		$socket = new ReactServer('0.0.0.0:8043', $loop);
		
		// Wrap the socket in a secure SSL/TLS layer
		$secureSocket = new SecureServer($socket, $loop, [
			'local_cert' => '/etc/letsencrypt/live/01.youds.dev/fullchain.pem', // Path to SSL certificate
			'local_pk' => '/etc/letsencrypt/live/01.youds.dev/privkey.pem', // Path to private key
			'allow_self_signed' => true, // Allow self-signed certificates (for testing)
			'verify_peer' => false, // Disable peer verification (for testing)
		]);

		// Wrap your WebSocket application
		$webServer = new IoServer(
			new HttpServer(
				new WsServer(
					$ws
				)
			),
			$secureSocket,
			$loop
		);

		// Run the event loop
		$loop->run();
	}
	
	
}



?>