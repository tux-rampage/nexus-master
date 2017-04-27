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

use Rampage\Nexus\Entities\DeployTarget;
use Rampage\Nexus\Entities\VHost;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Exception\Http\BadRequestException;

use Psr\Http\Message\ServerRequestInterface;

use Zend\Stdlib\Parameters;


/**
 * Implements the vhost rest service
 */
class VHostsService extends AbstractService
{
    /**
     * @param ServerRequestInterface $request
     * @param DeployTarget $target
     * @return VHost
     */
    private function findVhost(ServerRequestInterface $request, $target)
    {
        if (!$target) {
            return null;
        }

        return $target->getVHost($request->getAttribute('vhostId'));
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function get(ServerRequestInterface $request)
    {
        $target = $this->context->get($request);
        $id = $request->getAttribute('vhostId');

        if (!$target) {
            return null;
        }

        if (!$id) {
            return $target->getVHosts();
        }

        return $target->getVHost($id);
    }

    /**
     * @param ServerRequestInterface $request
     * @throws LogicException
     * @return NULL|\Rampage\Nexus\Entities\DeployTarget
     */
    public function delete(ServerRequestInterface $request)
    {
        $target = $this->context->get($request);
        $vhost = $this->findVhost($request, $target);

        if (!$vhost) {
            return null;
        }

        $target->removeVHost($vhost);
        $this->save($target);

        return $target;
    }

    /**
     * @param ServerRequestInterface $request
     * @return VHost|null
     */
    public function put(ServerRequestInterface $request)
    {
        $target = $this->context->get($request);
        $vhost = $this->findVhost($request, $target);

        if (!$vhost) {
            return null;
        }

        $vhost->exchangeArray($request->getParsedBody());
        $this->save($target);

        return $vhost;
    }

    /**
     * Create a new vhost
     *
     * @param ServerRequestInterface $request
     * @throws BadRequestException
     * @return NULL|\Rampage\Nexus\Entities\VHost
     */
    public function post(ServerRequestInterface $request)
    {
        $target = $this->context->get($request);

        if (!$target) {
            return null;
        }

        $data = new Parameters($request->getParsedBody());
        $vhost = new VHost($data->get('name'));

        $vhost->exchangeArray($data->toArray());

        if (!$vhost->getName()) {
            throw new BadRequestException('The vhost name must not be empty');
        } else if ($target->hasVHostName($vhost->getName())) {
            throw new BadRequestException('Duplicate hostname', BadRequestException::ENTITY_EXISTS);
        }

        $target->addVHost($vhost);
        $this->save($target);

        return $vhost;
    }
}