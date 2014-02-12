<?php

/**
 * TechDivision\Socket\Server
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_Socket
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\Socket;

use TechDivision\Socket\Client;

/**
 * The server socket implementation.
 *
 * @category   Appserver
 * @package    TechDivision_Socket
 * @subpackage Socket
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class Server extends Client
{

    /**
     * Starts a socket server listen to the specified IP address and port.
     *
     * @return \TechDivision\Socket\Server The server instance itself
     */
    public function start()
    {

        // create, bind and listen
        $this->create()
             ->setBlock()
             ->setReuseAddr()
             ->bind()
             ->listen();

        // return the instance
        return $this;
    }
}
