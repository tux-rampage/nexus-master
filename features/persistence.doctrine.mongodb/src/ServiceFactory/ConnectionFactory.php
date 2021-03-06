<?php
/**
 * Copyright (c) 2016 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\ODM\ServiceFactory;

use Rampage\Nexus\ServiceFactory\RuntimeConfigTrait;

use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\Configuration;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;


/**
 * Connection service factory
 */
class ConnectionFactory implements FactoryInterface
{
    use RuntimeConfigTrait;

    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $runtimeConfig = $this->getRuntimeConfig($container);
        $server = $runtimeConfig->get('odm.mongodb.server');
        $options = $runtimeConfig->get('odm.mongodb.options', []);
        $driverOptions = $runtimeConfig->get('odm.mongodb.driveroptions', []);
        $config = new Configuration();

        $config->setRetryConnect((int)$runtimeConfig->get('odm.mongodb.retryReconnect', 0));
        $config->setRetryQuery((int)$runtimeConfig->get('odm.mongodb.retryQuery', 0));

        $connection = new Connection($server, $options, $config, null, $driverOptions);

        return $connection;
    }
}
