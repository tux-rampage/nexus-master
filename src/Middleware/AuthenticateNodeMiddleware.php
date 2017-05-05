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

namespace Rampage\Nexus\Master\Middleware;

use Rampage\Nexus\NullStream;
use Rampage\Nexus\Repository\NodeRepositoryInterface;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;


/**
 * Implements authentication for nodes
 */
class AuthenticateNodeMiddleware
{
    /**
     * @var NodeRepositoryInterface
     */
    private $repository;

    /**
     * @param NodeRepositoryInterface $repository
     */
    public function __construct(NodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    private function getBasicCredentials(ServerRequestInterface $request)
    {
        $auth = $request->getHeaderLine('Authorization');

        if (!$auth || (substr(strtolower($auth), 0, 6) !== 'basic ')) {
            return null;
        }

        $credentials = trim(substr($auth, 6));
        $credentials = base64_decode($credentials);

        if (!$credentials) {
            return null;
        }

        $credentials = explode(':', $credentials, 2);

        if (count($credentials) != 2) {
            $credentials[] = null;
        }

        return $credentials;
    }

    /**
     * @param ServerRequestInterface $request
     * @param callable $next
     * @return \Zend\Diactoros\Response|unknown
     */
    public function __invoke(ServerRequestInterface $request, callable $next = null)
    {
        $credentials = $this->getBasicCredentials($request);
        if (!$credentials) {
            return new Response(new NullStream(), 401);
        }

        @list($nodeId, $secret) = $credentials;
        $node = $this->repository->findOne((string)$nodeId);

        if (!$node || !($node->getSecret() != $secret)) {
            return new Response(new NullStream(), 401);
        }

        return $next($request->withAttribute('node', $node));
    }
}