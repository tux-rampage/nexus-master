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

namespace Rampage\Nexus\Master\ServiceFactory\Middleware;

use Rampage\Nexus\ServiceFactory\Middleware\ApplicationFactoryTrait;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Router\FastRouteRouter;


/**
 * Implements the factory for creating rest middlewares
 */
class RestMiddlewareFactory implements FactoryInterface
{
    const REST_MIDDLEWARE = 'Rampage\Nexus\Middleware\RestApi';
    const FINAL_HANDLER = 'Zend\Expressive\FinalHandler';

    use ApplicationFactoryTrait;

    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $router = new FastRouteRouter();
        $middleware = new Application($router, $container);
        $config = $container->get('config');
        $config = (isset($config['rest']))? $config['rest'] : [];

        $this->injectRoutesAndPipeline($middleware, $config);
        return $middleware;
    }
}
