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

use Doctrine\Common\Collections\ArrayCollection;
use Rampage\Nexus\Exception\InvalidArgumentException;


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
     * @var ArrayCollection|JobConfig[]
     */
    protected $jobs = [];

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
     * @return \Closure
     */
    private function getJobNamePredicate($name)
    {
        return function(JobConfig $job) use ($name) {
            return ($job->getName() == $name);
        };
    }

    /**
     * @param string $name
     * @return JobConfig|null
     */
    public function getJobConfig($name)
    {
        return $this->jobs->filter($this->getJobNamePredicate($name))->first();
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasJob($name)
    {
        return $this->jobs->exists($this->getJobNamePredicate($name));
    }

    /**
     * Add a job config
     *
     * @param JobConfig $job
     */
    public function addJobConfig(JobConfig $job)
    {
        if ($this->hasJob($job->getName())) {
            throw new InvalidArgumentException(sprintf(
                'Duplicate job config for %s in instance %s',
                $job->getName(), $this->getLabel()
            ));
        }

        $this->jobs->add($job);
        return $this;
    }

    /**
     * Ensures the existence of a job config
     *
     * @param string $name
     * @return JobConfig
     */
    public function ensureJobConfig($name)
    {
        if ($this->hasJob($name)) {
            return $this->getJobConfig($name);
        }

        $job = new JobConfig($name);
        $this->addJobConfig($job);

        return $job;
    }

    /**
     * @return JobConfig[]
     */
    public function getAllJobs()
    {
        return $this->jobs;
    }

    /**
     * @param bool $excludedFlag
     * @return \Closure
     */
    private function getIncludeExcludePredicate($excludedFlag)
    {
        $excludedFlag = (bool)$excludedFlag;
        return function(JobConfig $item) use ($excludedFlag) {
            return ($item->isExcluded() == $excludedFlag);
        };
    }

    /**
     * @return JobConfig[]
     */
    public function getIncludedJobs()
    {
        return $this->jobs->filter($this->getIncludeExcludePredicate(false));
    }

    /**
     * @return JobConfig[]
     */
    public function getExcludedJobs()
    {
        return $this->jobs->filter($this->getIncludeExcludePredicate(true));
    }
}
