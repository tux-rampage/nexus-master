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

namespace Rampage\Nexus\Master\CI\Jenkins\Entities;

/**
 * Job configuration
 */
class JobConfig
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $exclude;

    /**
     * @var int[]
     */
    protected $processedBuildIds = [];

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isExcluded()
    {
        return $this->exclude;
    }

    /**
     * @param boolean $exclude
     * @return self
     */
    public function setExclude($exclude)
    {
        $this->exclude = (bool)$exclude;
        return $this;
    }

    /**
     * @return multitype:\Rampage\Nexus\Master\CI\Jenkins\Entities\int
     */
    public function getProcessedBuildIds()
    {
        return $this->processedBuildIds;
    }

    /**
     * @param int $buildId
     * @return self
     */
    public function addProcessedBuildId($buildId)
    {
        $this->processedBuildIds[] = $buildId;
        return $this;
    }
}
