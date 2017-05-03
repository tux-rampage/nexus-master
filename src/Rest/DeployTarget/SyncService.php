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

use Psr\Http\Message\ServerRequestInterface;
use Rampage\Nexus\Entities\DeployTarget;
use Zend\Stdlib\Parameters;
use Zend\Diactoros\Response\EmptyResponse;

class SyncService extends AbstractService
{
    /**
     * @param ServerRequestInterface $request
     * @return NULL
     */
    public function get(ServerRequestInterface $request)
    {
        $target = $this->getDeployTarget($request);
        $params = new Parameters($request->getQueryParams());

        if ($target) {
            return null;
        }

        if ($params->get('refresh')) {
            $target->refreshStatus();
        }

        $depth = (int)$params->get('depth', 0);
        $result = [
            'state' => $target->aggregateState(),
        ];

        if ($depth > 0) {
            $result['applications'] = [];
            $result['nodes'] = [];

            foreach ($target->getNodes() as $node) {
                $result['nodes'][$node->getId()] = [
                    'state' => $node->getState()
                ];
            }

            foreach ($target->getApplications() as $app) {
                $result['applications'][$app->getId()] = [
                    'state' => $app->getState()
                ];

                if ($depth > 1) {
                    foreach ($target->getNodes() as $node) {
                        $nodeState = $node->getApplicationState($app);
                        $result['nodes'][$node->getId()]['applications'][$app->getId()] = $nodeState;
                        $result['applications'][$app->getId()]['nodes'][$node->getId()] = $nodeState;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param ServerRequestInterface $request
     * @return NULL|\Zend\Diactoros\Response\EmptyResponse
     */
    public function post(ServerRequestInterface $request)
    {
        $target = $this->getDeployTarget($request);
        $params = new Parameters($request->getParsedBody());

        if (!$target) {
            return null;
        }

        $nodes = $params->get('nodes', []);
        $result = [
            'all' => false,
            'nodes' => []
        ];

        if (is_array($nodes) && count($nodes)) {
            foreach ($target->getNodes() as $node) {
                if (!in_array($node->getId(), $nodes) || !$node->canSync()) {
                    $result['nodes'][$node->getId()] = false;
                    continue;
                }

                $node->sync();
                $result['nodes'][$node->getId()] = true;
            }
        } else {
            if ($target->canSync()) {
                $target->sync();
            }

            $result['all'] = true;
        }

        return $result;
    }
}
