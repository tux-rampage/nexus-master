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
use Rampage\Nexus\Master\Rest\DeployTargetService;
use Rampage\Nexus\Repository\PersistenceManagerInterface;


/**
 * Abstract deploy target context service
 */
abstract class AbstractService
{
    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var DeployTargetService
     */
    protected $context;

    /**
     * @param DeployTargetService $context
     * @param PersistenceManagerInterface $persistenceManager
     */
    public function __construct(DeployTargetService $context, PersistenceManagerInterface $persistenceManager)
    {
        $this->context = $context;
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Saves the given entity and performs a flush
     *
     * @param object $entity
     */
    protected function save($entity)
    {
        $this->persistenceManager->persist($entity);
        $this->persistenceManager->flush();
    }
}
