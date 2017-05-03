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

namespace Rampage\Nexus\Master;

use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Helper\UrlHelperMiddleware;
use Rampage\Nexus\Middleware\RestfulServiceMiddleware;

/**
 * Defines the routing config
 */
return [
    'rest' => [
        // This can be used to seed pre- and/or post-routing middleware
        'middleware_pipeline' => [
            'routing' => [
                'middleware' => [
                    'route' => ApplicationFactory::ROUTING_MIDDLEWARE,
                    'beforeDispatch' => [
                        'middleware' => [
                            OAuth2\Middleware\ResourceServerMiddleware::class,
                            UrlHelperMiddleware::class,
                        ]
                    ],
                    'dispatch' => ApplicationFactory::DISPATCH_MIDDLEWARE,
                ],
                'priority' => 1,
            ],
        ],
        'routes' => [
            'index' => [
                'name' => 'index',
                'path' => '/',
                'middleware' => Action\IndexAction::class,
                'allowed_methods' => ['GET'],
            ],

            'nodes' => [
                'name' => 'nodes',
                'path' => '/nodes[/{id}]',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\NodesService::class),
            ],

            'applications' => [
                'name' => 'applications',
                'path' => '/applications[/{id}]',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\ApplicationsService::class),
                'allow_methods' => [ 'GET', 'PUT', 'OPTIONS' ],
            ],

            'packages' => [
                'name' => 'packages',
                'path' => '/packages[/{id}]',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\PackagesService::class),
                'allow_methods' => [ 'GET', 'PUT', 'POST', 'DELETE', 'OPTIONS' ],
            ],

            'applications/icon' => [
                'name' => 'noauth:applications/icon',
                'path' => '/applications/{id}/icon',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\ApplicationIconService::class),
                'allow_methods' => [ 'GET', 'OPTIONS' ],
            ],

            'deploytargets' => [
                'name' => 'deploytargets',
                'path' => '/targets[/{id}]',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\DeployTargetService::class),
            ],

            'deploytargets/nodes' => [
                'name' => 'deploytargets/nodes',
                'path' => '/targets/{id}/nodes[/{nodeId}]',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\DeployTarget\NodesService::class),
            ],

            'deploytargets/applications' => [
                'name' => 'deploytargets/applications',
                'path' => '/targets/{id}/apps[/{applicationId}]',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\DeployTarget\ApplicationsService::class),
            ],

            'deploytargets/vhosts' => [
                'name' => 'deploytargets/vhosts',
                'path' => '/targets/{id}/vhosts[/{vhostId}]',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\DeployTarget\VHostsService::class),
            ],

            'deploytargets/sync' => [
                'name' => 'deploytargets/sync',
                'path' => '/targets/{id}/sync',
                'middleware' => RestfulServiceMiddleware::getMiddlewareServiceName(Rest\DeployTarget\SyncService::class),
            ],

        ],
    ]
];
