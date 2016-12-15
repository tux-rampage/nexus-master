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
 * Instance configuration
 */
class InstanceConfig
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $jenkinsUrl;

    /**
     * @var bool
     */
    private $scanArtifactFiles = false;

    /**
     * @var string[]
     */
    protected $includeProjects = [];

    /**
     * @param string $jenkinsUrl
     */
    public function __construct($jenkinsUrl)
    {
        $this->jenkinsUrl = $jenkinsUrl;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getJenkinsUrl()
    {
        return $this->jenkinsUrl;
    }

    /**
     * @param string $jenkinsUrl
     * @return self
     */
    public function setJenkinsUrl($jenkinsUrl)
    {
        $this->jenkinsUrl = (string)$jenkinsUrl;
        return $this;
    }

    /**
     * Included project names
     *
     * @return string[]
     */
    public function getIncludeProjects()
    {
        return $this->includeProjects;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param boolean $scanArtifactFiles
     * @return self
     */
    public function setScanArtifactFiles($scanArtifactFiles)
    {
        $this->scanArtifactFiles = $scanArtifactFiles;
        return $this;
    }

    /**
     * @param multitype:\Rampage\Nexus\Master\CI\Jenkins\Entities\string  $includeProjects
     * @return self
     */
    public function setIncludeProjects($includeProjects)
    {
        $this->includeProjects = $includeProjects;
        return $this;
    }

    /**
     * Checks if artifacts should be downloaded and scanned
     *
     * @return bool
     */
    public function isArtifactScanEnabled()
    {
        return $this->scanArtifactFiles;
    }

    /**
     * @param string $name
     * @return self
     */
    public function includeProject($name)
    {
        $this->includeProjects[] = (string)$name;
        return $this;
    }
}
