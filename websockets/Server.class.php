<?php
namespace YoudsFramework\WebSockets;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

/**
 * Youds Framework Web Socket Server Built on Ratchet
 *
 * @package default
 * @author Craig Fairhurst <craig.fairhurst@youds.com>
 */
class Server implements MessageComponentInterface {

	
	/**
	 * Clients
	 * 
	 * @var object Stored objects
	 */
    public $clients;
    
	public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
    
	public function onOpen (ConnectionInterface $conn) {
        $this->clients->attach($conn);
       	$this->connected($this->clients, $conn);
    }
    
	public function onMessage(ConnectionInterface $from, $message) {
        $this->message($this->clients, $from, $message);
    }
	
	public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->disconnected($this->clients, $conn);
    }
    
	public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
		$this->error($this->clients, $conn, $e);
    }
	
	public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }	
	
}



?>