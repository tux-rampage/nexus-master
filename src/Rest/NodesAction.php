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

namespace Rampage\Nexus\Master\Rest;

use Rampage\Nexus\Entities\Node;

use Rampage\Nexus\Repository\NodeRepositoryInterface;
use Rampage\Nexus\Repository\RestService\GetableTrait;
use Rampage\Nexus\Repository\RestService\PutableTrait;
use Rampage\Nexus\Repository\RestService\PostableTrait;

use Rampage\Nexus\Exception\Http\BadRequestException;
use Rampage\Nexus\Exception\RuntimeException;
use Rampage\Nexus\Repository\PersistenceManagerInterface;


/**
 * Implements the rest service contract for Nodes
 */
class NodesService
{
    use GetableTrait;
    use PutableTrait;
    use PostableTrait;

    /**
     * @param NodeRepositoryInterface $repository
     */
    public function __construct(NodeRepositoryInterface $repository, PersistenceManagerInterface $peristenceManager)
    {
        $this->repository = $repository;
        $this->persistenceManager = $peristenceManager;
    }

    /**
     * @param array $data
     * @throws BadRequestException
     * @return \Rampage\Nexus\Entities\Node
     */
    private function createNewEntity(array $data)
    {
        try {
            /** @var $node \Rampage\Nexus\Entities\Node */
            $node = $this->repository->getPrototypeByData($data);
            $node->getStrategy();
        } catch (RuntimeException $e) {
            throw new BadRequestException($e->getMessage(), BadRequestException::UNPROCESSABLE, $e);
        }

        $node->exchangeArray($data);
        return $node;
    }
}
