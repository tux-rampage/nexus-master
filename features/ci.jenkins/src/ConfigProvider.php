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

namespace Rampage\Nexus\Master\CI\Jenkins;

use Rampage\Nexus\Config\PropertyConfigInterface;
use Rampage\Nexus\Archive\ArchiveLoader;


/**
 * Returns the jekins configuration
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'dependencies' => [
                'delegators' => [
                    ArchiveLoader::class => [
                        'jenkins' => ServiceFactory\ArchiveLoaderDelegator::class,
                    ]
                ]
            ],
            'di' => [
                'preferences' => [
                    ClientFactoryInterface::class => ClientFactory::class,
                    Repository\InstanceRepositoryInterface::class => Repository\ConfiguredInstancesRepository::class,
                    PackageScanner\PackageScannerInterface::class => PackageScanner\PackageScanner::class,
                ],

                'instances' => [
                    Repository\ConfiguredInstancesRepository::class => [
                        'preferences' => [
                            PropertyConfigInterface::class => 'RuntimeConfig',
                        ]
                    ]
                ]
            ],

            'commands' => [
                'jenkins:scan' => Console\ScanCommand::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return $this->getConfig();
    }
}