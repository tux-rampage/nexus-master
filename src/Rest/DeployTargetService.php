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

namespace Rampage\Nexus\Master\Rest;

use Rampage\Nexus\Repository\RestService\GetableTrait;
use Rampage\Nexus\Repository\RestService\PutableTrait;
use Rampage\Nexus\Repository\RestService\PostableTrait;
use Rampage\Nexus\Repository\RestService\DeletableTrait;
use Rampage\Nexus\Entities\DeployTarget;
use Rampage\Nexus\Repository\DeployTargetRepositoryInterface;
use Rampage\Nexus\Repository\PersistenceManagerInterface;

class DeployTargetService
{
    use GetableTrait;
    use PutableTrait;
    use PostableTrait;
    use DeletableTrait;

    /**
     * @var DeployTargetRepositoryInterface
     */
    private $repository;

    /**
     * @param DeployTargetRepositoryInterface $repository
     * @param PersistenceManagerInterface $persistenceManager
     */
    public function __construct(DeployTargetRepositoryInterface $repository, PersistenceManagerInterface $persistenceManager)
    {
        $this->repository = $repository;
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param array $data
     * @return \Rampage\Nexus\Entities\DeployTarget
     */
    private function createNewEntity(array $data)
    {
        return new DeployTarget();
    }
}