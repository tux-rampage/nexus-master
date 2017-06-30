<?php
/**
 * Copyright (c) 2017 Axel Helmert
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
 * @copyright Copyright (c) 2017 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\ODM\ServiceFactory;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Interop\Container\ContainerInterface;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stdlib\Parameters;


/**
 * Creates the mapping driver instance
 */
class MappingDriverFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $sysconfig = $container->get('config');
        $params = new Parameters(isset($sysconfig['odm'])? $sysconfig['odm'] : []);
        $drivers = $params->get('mappingDrivers');
        $driverChain = new MappingDriverChain();

        if (!is_array($drivers)) {
            $drivers = [];
        }

        foreach ($drivers as $namespace => $driver) {
            $driverChain->addDriver($container->get($driver), $namespace);
        }

        return $driverChain;
    }
}