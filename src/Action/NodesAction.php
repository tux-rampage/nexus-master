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

namespace Rampage\Nexus\Master\Action;

use Rampage\Nexus\Repository\NodeRepositoryInterface;
use Rampage\Nexus\Entities\Node;
use Rampage\Nexus\Exception\Http\BadRequestException;
use Rampage\Nexus\Master\Deployment\NodeStrategyProvider;
use Rampage\Nexus\Exception\RuntimeException;

class NodesAction extends AbstractRestAction
{
    private $nodeStrategyProvider;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Action\AbstractRestApi::__construct()
     */
    public function __construct(NodeRepositoryInterface $repository, NodeStrategyProvider $nodeStrategyProvider)
    {
        parent::__construct($repository);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\Action\AbstractRestAction::newEntityInstance()
     */
    protected function newEntityInstance(array $data)
    {
        try {
            /** @var $node \Rampage\Nexus\Entities\Node */
            $node = $this->repository->getPrototypeByData($data);
            $node->getStrategy();
        } catch (RuntimeException $e) {
            throw new BadRequestException($e->getMessage(), BadRequestException::UNPROCESSABLE, $e);
        }

        return $node;
    }
}
