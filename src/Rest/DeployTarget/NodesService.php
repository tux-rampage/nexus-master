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

namespace Rampage\Nexus\Master\Rest\DeployTarget;

use Rampage\Nexus\Entities\Node;
use Rampage\Nexus\Entities\DeployTarget;

use Rampage\Nexus\Repository\PersistenceManagerInterface;
use Rampage\Nexus\Repository\NodeRepositoryInterface;

use Rampage\Nexus\Master\Rest\DeployTargetService;
use Rampage\Nexus\Deployment\NodeStrategyProviderInterface;
use Rampage\Nexus\Exception\Http\BadRequestException;

use Psr\Http\Message\ServerRequestInterface;
use Rampage\Nexus\Exception\RuntimeException;


/**
 * Implements the node rest service
 */
class NodesService extends AbstractService
{
    /**
     * @var NodeRepositoryInterface
     */
    private $repository;

    /**
     * @var NodeStrategyProviderInterface
     */
    private $strategyProvider;

    /**
     * @param DeployTargetService $context
     * @param PersistenceManagerInterface $persistenceManager
     * @param NodeStrategyProviderInterface $strategyProvider
     */
    public function __construct(
        DeployTargetService $context,
        NodeRepositoryInterface $repository,
        PersistenceManagerInterface $persistenceManager,
        NodeStrategyProviderInterface $strategyProvider)
    {
        parent::__construct($context, $persistenceManager);

        $this->repository = $repository;
        $this->strategyProvider = $strategyProvider;
    }

    /**
     * @param RequestInterface $request
     * @return Node
     */
    public function get(ServerRequestInterface $request)
    {
        /** @var DeployTarget $target */
        $target = $this->context->get($request);
        $nodeId = $request->getAttribute('nodeId');

        if (!$nodeId) {
            return $target->getNodes();
        }

        foreach ($target->getNodes() as $node) {
            if ($node->getId() == $nodeId) {
                return $node;
            }
        }

        return null;
    }

    /**
     * @param ServerRequestInterface $request
     * @return DeployTarget
     */
    public function put(ServerRequestInterface $request)
    {
        /** @var DeployTarget $target */
        $target = $this->context->get($request);
        $data = $request->getParsedBody();

        if (!$target || !isset($data['rebuildIds']) || !is_array($data['rebuildIds'])) {
            return null;
        }

        foreach ($target->getNodes() as $node) {
            if (in_array($node->getId(), $data['rebuildIds'])) {
                $node->rebuild();
                $this->save($node);
            }
        }

        return $target;
    }

    /**
     * Attaches a node to the deploy target in context
     *
     * After the node is attached a sync is triggered if possible
     *
     * @param ServerRequestInterface $request
     * @throws BadRequestException
     * @return NULL|\Rampage\Nexus\Entities\Node
     */
    public function post(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();

        if (!isset($data['id'])) {
            throw new BadRequestException('Missing node id');
        }

        /** @var Node $node */
        $target = $this->context->get($request);
        $node = $this->repository->findOne($data['id']);

        if (!$target || !$node) {
            return null;
        }

        $node->setStrategyProvider($this->strategyProvider);
        $node->attach($target);

        $this->save($node);

        if ($node->canSync()) {
            try {
                $node->rebuild();
            } catch (RuntimeException $e) {
            }

            $this->save($node);
        }

        return $node;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Rampage\Nexus\Entities\Node
     */
    public function delete(ServerRequestInterface $request)
    {
        $node = $this->get($request);

        if ($node instanceof Node) {
            $node->setStrategyProvider($this->strategyProvider);
            $node->detach();
            $this->save($node);
        }

        return $node;
    }
}