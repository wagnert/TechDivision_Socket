<?php

/**
 * TechDivision\Socket\Server
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\Socket;

use TechDivision\Socket\Client;

/**
 * The server socket implementation.
 *
 * @package     TechDivision\Socket
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class Server extends Client {

    /**
     * Starts a socket server listen to the specified IP address and port.
     *
     * @return \TechDivision\Socket\Server The server instance itself
     */
    public function start() {

        // create, bind and listen
        $this->create()
             ->setBlock()
             ->setReuseAddr()
             ->setReceiveTimeout()
             ->bind()
             ->listen();

        // return the instance
        return $this;
    }
}