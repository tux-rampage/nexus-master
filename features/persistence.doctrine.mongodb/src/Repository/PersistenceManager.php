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

namespace Rampage\Nexus\ODM\Repository;

use Rampage\Nexus\Repository\PersistenceManagerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PersistenceManager implements PersistenceManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $documentManager;

    /**
     * @param ObjectManager $documentManager
     */
    public function __construct(ObjectManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PersistenceManagerInterface::flush()
     */
    public function flush($object = null)
    {
        $this->documentManager->flush($object);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PersistenceManagerInterface::persist()
     */
    public function persist($object)
    {
        $this->documentManager->persist($object);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PersistenceManagerInterface::remove()
     */
    public function remove($object)
    {
        $this->documentManager->remove($object);
    }
}
