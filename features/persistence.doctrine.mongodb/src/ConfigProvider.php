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

namespace Rampage\Nexus\ODM;

use Rampage\Nexus\Master\OAuth2\Repository\UserRepositoryInterface;

use Doctrine\MongoDB\Connection as ODMConnection;
use Doctrine\ODM\MongoDB\Configuration as ODMConfig;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

use League\OAuth2\Server\Repositories\UserRepositoryInterface as OAuthUserRepoInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Rampage\Nexus\Repository\PersistenceManagerInterface;


/**
 * Implements the config provider
 */
class ConfigProvider
{
    /**
     * Provides the config
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    DocumentManager::class => ServiceFactory\DocumentManagerFactory::class,
                    ODMConnection::class => ServiceFactory\ConnectionFactory::class,
                    ODMConfig::class => ServiceFactory\ConfigFactory::class,
                    MappingDriver::class => ServiceFactory\MappingDriverFactory::class
                ],

                'aliases' => [
                    AccessTokenRepositoryInterface::class => OAuth2\Repository\AccessTokenRepository::class,
                    RefreshTokenRepositoryInterface::class => OAuth2\Repository\RefreshTokenRepository::class,
                    UserRepositoryInterface::class => OAuth2\Repository\UserRepository::class,
                    OAuthUserRepoInterface::class => OAuth2\Repository\UserRepository::class,
                    PersistenceManagerInterface::class => Repository\PersistenceManager::class,
                ],
            ],

            'odm' => [
                'mappingDrivers' => [
                    'Rampage\Nexus\Entities' => Mapping\Driver::class,
                ]
            ],
        ];
    }

}
