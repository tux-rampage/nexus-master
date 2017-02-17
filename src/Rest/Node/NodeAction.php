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

namespace Rampage\Nexus\Master\Rest\Node;

use Rampage\Nexus\Entities\Node;
use Rampage\Nexus\Repository\NodeRepositoryInterface;

use Psr\Http\Message\ServerRequestInterface;

use Zend\Diactoros\Response\EmptyResponse;
use Zend\Stdlib\Parameters;


/**
 * Implements the primary node service contract
 *
 * This service will perform node updates on put requests and
 * Return the deploy target information for get requests
 */
class NodeService
{
    use NodeContextTrait;

    /**
     * @var NodeRepositoryInterface
     */
    protected $repository;

    /**
     * @param NodeRepositoryInterface $repository
     */
    public function __construct(NodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $data
     */
    protected function updateNodeState(Node $node, array $data)
    {
        $params = new Parameters($data);
        $node->updateState($params->get('state', $node->getState()), $params->get('applicationsState'));
        $this->repository->save($node);
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Rampage\Nexus\Entities\Node
     */
    public function get(ServerRequestInterface $request)
    {
        return $this->ensureNodeContext($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Zend\Diactoros\Response\EmptyResponse
     */
    public function put(ServerRequestInterface $request)
    {
        $node = $this->ensureNodeContext($request);

        $this->updateNodeState($node, $this->decodeJsonRequestBody($request));
        return new EmptyResponse();
    }
}
