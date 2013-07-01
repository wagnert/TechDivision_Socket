<?php

/**
 * TechDivision_Socket
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision;

use TechDivision\SocketException;

/**
 * The socket implementation.
 *
 * @package     TechDivision
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 * @author      Markus Stockbauer <ms@techdivision.com>
 */
class Socket {

    /**
     * The *nix socket error number for Resource Temporarily Unavailable state.
     * @var integer
     */
    const SOCKET_ERROR_RESOURCE_TEMPORARILY_UNAVAILABLE = 11;

    /**
     * Times to retry reading from one socket if error 11 occurs.
     * @var integer
     */
    const SOCKET_READ_RETRY_COUNT = 10;

    /**
     * The time to wait between two read attempts on one socket in microseconds (here: 0.1 sec).
     * @var integer
     */
    const SOCKET_READ_RETRY_WAIT_TIME_USEC = 100000;

    /**
     * The socket resource.
     * @var resource
     */
    protected $resource = null;
    
    /**
     * The default address the socket listens to.
     * @var string
     */
    protected $address = '127.0.0.1';

    /**
     * The default port the socket listens to.
     * @var int
     */
    protected $port = 0;

    /**
     * A maximum of backlog incoming connections will be queued for processing.
     *
     * If a connection request arrives with the queue full the client may receive an error with an indication
     * of ECONNREFUSED, or, if the underlying protocol supports retransmission, the request may be ignored
     * so that retries may succeed.
     *
     * @var integer
     */
    protected $backlog = 100;
    
    /**
     * 
     * @var unknown
     */
    protected $blocking = false;

    /**
     * Initializes the socket instance with the socket resource.
     *
     * @param resource $resource The socket resource
     * @return \TechDivision\Socket
     */
    public function __construct($resource = null) {
        $this->setResource($resource);
    }

    /**
     * Set's the socket resource to use.
     *
     * @param resource $resource The socket resource to use
     * @return Socket The socket instance itself
     */
    public function setResource($resource) {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Returns the socket resource used to connect to the socket.
     *
     * @return resource The socket resource
     */
    public function getResource() {
        return $this->resource;
    }

    /**
     * Set's the address the socket listens to.
     *
     * @param string $address The address the socket listens to
     * @return Socket The socket instance itself
     */
    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    /**
     * Return's the address the socket listens to.
     *
     * @return string The address the socket listens to
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Sets the port the socket listens to.
     *
     * @param integer $port The port the socket listens to
     * @return Socket The socket instance itself
     */
    public function setPort($port) {
        $this->port = $port;
        return $this;
    }

    /**
     * Returns the port the socket listens to.
     *
     * @return integer The port the socket listens to.
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Set's the maximum backlog number of incoming connections will be queued for processing.
     *
     * @param integer $backlog The maximum backlog number of incoming connections
     * @return Socket The socket instance itself
     */
    public function setBacklog($backlog) {
        $this->backlog = $backlog;
        return $this;
    }

    /**
     * Return's the socket's backlog that specifies the maximum of backlog incoming connections that will be queued for processing.
     * @return int
     * @link http://http://de3.php.net/socket_listen
     */
    public function getBacklog() {
        return $this->backlog;
    }

    /**
     * This method create's a socket (endpoint for communication) by calling the socket function {@link http://de3.php.net/socket_create socket_create()}.
     *
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_create
     */
    public function create() {

        // create new socket
        if (($this->resource = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            $this->newSocketException();
        }

        // return the instance itself
        return $this;
    }
    
    /**
     * Returns TRUE if the socket is in blocking mode, else FALSE.
     * 
     * @return boolean TRUE if the socket is in blocking mode, else FALSE
     */
    public function isBlocking() {
        return $this->blocking;
    }

    /**
     * This method set's the socket in blocking mode by calling the socket function {@link http://de3.php.net/socket_set_block socket_set_block()}.
     *
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_set_block
     */
    public function setBlock() {

        // activate blocking mode
        $this->blocking = true;
        
        if (@socket_set_block($this->resource) === false) {
            $this->newSocketException();
        }

        // return the instance itself
        return $this;
    }

    /**
     * This method set's the socket in non-blocking mode by calling the socket function {@link http://de3.php.net/socket_set_nonblock socket_set_nonblock()}.
     *
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_set_nonblock
     */
    public function setNoBlock() {

        // activate non blocking mode
        $this->blocking = false;
        
        // set the socket in non-blocking mode
        if (@socket_set_nonblock($this->resource) === false) {
            $this->newSocketException();
            
        }

        // return the instance itself
        return $this;
    }

    /**
     * This method set's whether the local addresses can be reused by calling the socket function {@link http://de3.php.net/socket_set_option socket_set_option()}.
     *
     * @param integer $reuse Has to be 1 if the address can be reused, else false
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_set_option
     */
    public function setReuseAddr($reuse = 1) {
        return $this->setOption(SOL_SOCKET, SO_REUSEADDR, $reuse);
    }

    /**
     * This method sets the timeout value for input operations by calling the socket function {@link http://de3.php.net/socket_set_option socket_set_option()}.
     *
     * @param integer $seconds The seconds part on the timeout
     * @param integer $microseconds The microseconds part on the timeout
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_set_option
     */
    public function setReceiveTimeout($seconds = 0, $microseconds = 100) {
        return $this->setOption(SOL_SOCKET, SO_RCVTIMEO, array("sec" => $seconds, "usec" => $microseconds));
    }

    /**
     * This method set's the socket's lingering option by calling the socket function {@link http://de3.php.net/socket_set_option socket_set_option()}.
     *
     * When an application program indicates that a socket is to linger, it also specifies a duration for the
     * lingering period. If the lingering period expires before the disconnect is completed, the socket layer
     * forcibly shuts down the socket, discarding any data still pending.
     *
     * If onOff is non-zero and linger is zero, all the unsent data will be discarded and RST (reset) is sent to
     * the peer in the case of a connection-oriented socket. On the other hand, if onOff is non-zero and linger is
     * non-zero, {@link http://de3.php.net/socket_close socket_close()} will block until all the data is sent or the
     * time specified in l_linger elapses. If the socket is non-blocking, {@link http://de3.php.net/socket_close socket_close()}
     * will fail and return an error.
     *
     * @param integer $onOff Switches lingering on if integer is passed that is non-zero
     * @param integer $linger By setting this to non-zero {@link http://de3.php.net/socket_close socket_close()} will block until all the data is sent or the timeout elapses
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_set_option
     */
    public function setLinger($onOff = 1, $linger = 1) {
        return $this->setOption(SOL_SOCKET, SO_LINGER, array('l_onoff' => $onOff, 'l_linger' => $linger));
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_close socket_close()}.
     * The method closes a socket resource.
     *
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_close
     */
    public function close() {

        // try to close the socket
        if (@socket_close($this->resource) === false) {
            throw $this->newSocketException();
            
        }

        // return the socket instance itself
        return $this;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_shutdown socket_shutdown()}.
     * The method shuts down a socket for receiving, sending, or both.
     *
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_shutdown
     */
    public function shutdown() {

        // try to shutdown the socket
        if (@socket_shutdown($this->resource) === false) {
            throw $this->newSocketException();
        }

        // return the socket instance itself
        return $this;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_connect socket_connect()}.
     * The method initiates a connection on a socket
     *
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_connect
     */
    public function connect() {

        // connect to a socket
        if (@socket_connect($this->resource, $this->getAddress(), $this->getPort()) === false) {
            throw $this->newSocketException();
        }

        // return the socket instance itself
        return $this;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_send socket_send()}.
     * The method sends data to a connected socket.
     *
     * @param string $data The data to send over the socket
     * @return integer The number of bytes send over the socket
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_write
     */
    public function send($data) {

        // try to send the data to the socket
        if (($bytesSend = @socket_write($this->resource, $data)) === false) {
            throw $this->newSocketException();
        }

        // return the number of bytes sent
        return $bytesSend;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_accept socket_accept()}.
     * The method accepts a new connection on the socket.
     *
     * @return Socket A new client socket
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_bind
     */
    public function bind() {
        
        // bind the source address
        if (@socket_bind($this->resource, $this->getAddress(), $this->getPort()) === false) {

            // temporarily store the error code to throw a nested exception maybe
            $errorCode = socket_last_error($this->resource);

            try {

                // try to close the socket
                $this->close();

            } catch(SocketException $previous) {

                // throw a nested exception
                throw $this->newSocketException($errorCode, $previous);
            }

            // throw a normal exception if the socket has successfully been closed
            throw $this->newSocketException($errorCode);
        }

        // return the socket instance itself
        return $this;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_listen socket_listen()}.
     * The method listens for a connection on a socket.
     *
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occurred
     * @link http://de3.php.net/socket_listen
     */
    public function listen() {

        // list to the socket
        if (@socket_listen($this->resource, $this->getBacklog()) === false) {

            // temporarily store the error code to throw a nested exception maybe
            $errorCode = socket_last_error($this->resource);

            try {

                // try to close the socket
                $this->close();

            } catch(SocketException $previous) {

                // throw a nested exception
                throw $this->newSocketException($errorCode, $previous);
            }

            // throw a normal exception if the socket has successfully been closed
            throw $this->newSocketException($errorCode);
        }

        // return the socket instance itself
        return $this;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_select socket_select()}.
     * The method accepts a new connection on the socket.
     *
     * The timeoutSeconds and timeoutMicroseconds together form the timeout parameter. The timeout is an upper
     * bound on the amount of time elapsed before {@link http://de3.php.net/socket_select socket_select()} return.
     * timeoutSeconds may be zero , causing {@link http://de3.php.net/socket_select socket_select()} to return
     * immediately. This is useful for polling. If timeoutSeconds is NULL (no timeout), {@link http://de3.php.net/socket_select socket_select()}
     * can block indefinitely.
     *
     * @param array $read The sockets listed in the read array will be watched to see if characters become available for reading
     * @param array $write The sockets listed in the write array will be watched to see if a write will not block
     * @param array $except The sockets listed in the except array will be watched for exceptions
     * @param integer $timeoutSeconds Timeout in seconds, or null if no timeout should be used
     * @param int $timeoutMicroseconds Timeout in microseconds
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occured
     */
    public function select(&$read, &$write, &$except, $timeoutSeconds = null, $timeoutMicroseconds = 0) {

        // now call select - blocking call
        if (@socket_select($read, $write, $except, $timeoutSeconds, $timeoutMicroseconds) === false) {
            throw $this->newSocketException();
        }

        // return the socket instance itself
        return $this;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_accept socket_accept()}.
     * The method accepts a new connection on the socket.
     *
     * @return Socket A new client socket
     * @throws SocketException Is thrown if an failure occured
     * @link http://de3.php.net/socket_accept
     */
    public function accept() {

        // accept a new incoming connection
        $client = @socket_accept($this->resource);

        // check if a new incoming connection has been accepted
        if ($client === false && $this->isBlocking()) {
            throw $this->newSocketException();
        } elseif ($client === false && $this->isBlocking() === false) {
            return false;
        }

        // return a new client socket instance
        return new Socket($client);
    }

    /**
     * OO Wrapper for PHP's {@link http://de3.php.net/socket_read socket_read()} function. Except for the
     * "Resource temporarily unavailable" error, only an Exception is thrown. Typed exception is thrown
     * to allow handling of that error, since it should be treated in a special way.
     *
     * For regular workflow, it is necessary to allow retrying a read operation on a socket which
     * signals that it is not ready yet. At least under Linux, this is an acceptable
     * and normal condition. It is not sufficient to just end the connection and continue in the
     * main loop, since the original client connection would hang indefinitely until a timeout
     * occurs. Instead, it is necessary to retry reading while the socket is not ready yet, which
     * is usually no longer than a a few milliseconds.
     *
     * @param integer $length The maximum number of bytes read is specified by the length parameter
     * @param int $type Optional type parameter is a named constant, PHP_BINARY_READ (default) or PHP_NORMAL_READ
     * @throws SocketException Is thrown if a failure occured
     * @return string The string read from the socket
     * @link http://de3.php.net/socket_read
     */
    public function read($length, $type = PHP_BINARY_READ) {

        // record the number of read attempts
        $readAttempts = 0;

        // try to read data from the socket
        while (($result = @socket_read($this->resource, $length, $type)) === false) {

            // initialize error code and message
            $errorCode = socket_last_error();

            // check for the error code and retry if possible
            if ($errorCode == self::SOCKET_ERROR_RESOURCE_TEMPORARILY_UNAVAILABLE && $readAttempts++ < self::SOCKET_READ_RETRY_COUNT) {

                // sleep for a certain time
                usleep(self::SOCKET_READ_RETRY_WAIT_TIME_USEC);

                // continue with the next read attempt
                continue;
            }

            // on any other socket error, or if the max. number of read attempts has been reached, throw exception
            throw $this->newSocketException();
        }

        // return the string read from the socket
        return $result;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_getsockname socket_getsockname()}.
     * The method queries the local side of the socket.
     *
     * @param string $address The local address
     * @param integer $port The local port
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occurred
     * @link http://de3.php.net/socket_getsockname
     */
    public function getSockName(&$address, &$port) {

        // query the local socket
        if (@socket_getsockname($this->resource, $address, $port) === false) {
            throw $this->newSocketException();
        }

        // return the instance itself
        return $this;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_getpeername socket_getpeername()}.
     * The method queries the remote side of the socket.
     *
     * @param string $address The remote address
     * @param integer $port The remote port
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occurred
     * @link http://de3.php.net/socket_getpeername
     */
    public function getPeerName(&$address, &$port) {

        // query the remote socket
        if (@socket_getpeername($this->resource, $address, $port) === false) {
            throw $this->newSocketException();
        }

        // return the instance itself
        return $this;
    }

    /**
     * Wrapper method for the original socket function {@link http://de3.php.net/socket_set_option socket_set_option()}.
     * The method sets socket options for the socket.
     *
     * @param integer $level The option level to set
     * @param integer $optionName The option name to set
     * @param mixed $value The option value to set
     * @return Socket The socket instance itself
     * @throws SocketException Is thrown if an failure occurred
     * @link http://de3.php.net/socket_set_option
     */
    public function setOption($level, $optionName, $value) {

        // try to set the socket's receive timeout
        if (@socket_set_option($this->resource, $level, $optionName, $value) === false) {

            // temporarily store the error code to throw a nested exception maybe
            $errorCode = socket_last_error($this->resource);

            try {

                // try to close the socket
                $this->close();

            } catch(SocketException $previous) {

                // throw a nested exception
                throw $this->newSocketException($errorCode, $previous);
            }

            // throw a normal exception if the socket has successfully been closed
            throw $this->newSocketException($errorCode);
        }

        // the socket instance itself
        return $this;
    }

    /**
     * Returns a new socket exception initialized with the passed error message and the last
     * found socket error.
     *
     * @param integer $errorCode The error code to initialize the exception with
     * @param SocketException $se The previous exception if available
     * @return SocketException The initialized exception ready to be thrown
     */
    protected function newSocketException($errorCode = null, $se = null) {

        // initialize error code if no error code has been passed
        if ($errorCode == null) {
            $errorCode = socket_last_error();
        }

        // initialize the error message based on the code
        $errorMessage = socket_strerror($errorCode);

        // throw an exception
        return new SocketException($errorMessage, $errorCode, $se);
    }
}