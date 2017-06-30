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

namespace Rampage\Nexus\ODM\Repository\Jenkins;

use Rampage\Nexus\ODM\Repository\AbstractRepository;

use Rampage\Nexus\Master\CI\Jenkins\Repository\InstanceRepositoryInterface;
use Rampage\Nexus\Master\CI\Jenkins\Repository\StateRepositoryInterface;
use Rampage\Nexus\Master\CI\Jenkins\BuildNotification;
use Rampage\Nexus\Master\CI\Jenkins\Entities\InstanceConfig;
use Rampage\Nexus\Master\CI\Jenkins\Build;
use Rampage\Nexus\Master\CI\Jenkins\Job;

/**
 * Implements the instance repo
 */
class InstanceRepository extends AbstractRepository implements InstanceRepositoryInterface, StateRepositoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\ODM\Repository\AbstractRepository::getEntityClass()
     */
    protected function getEntityClass()
    {
        return InstanceConfig::class;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\CI\Jenkins\Repository\InstanceRepositoryInterface::find()
     */
    public function find($key)
    {
        $this->findOne($key);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\CI\Jenkins\Repository\InstanceRepositoryInterface::findByBuildNotification()
     */
    public function findByBuildNotification(BuildNotification $notification)
    {
        $url = rtrim($notification->getJenkinsUrl(), '/');

        return $this->getEntityRepository()->findBy([
            'jenkinsUrl' => $url
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\CI\Jenkins\Repository\StateRepositoryInterface::addProcessedBuild()
     */
    public function addProcessedBuild(InstanceConfig $config, Build $build)
    {
        $jobConfig = $config->getJobConfig($build->getJob()->getName());

        if ($jobConfig) {
            $jobConfig->addProcessedBuildId($build->getId());
            $this->objectManager->flush($config);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\CI\Jenkins\Repository\StateRepositoryInterface::getProcessedBuilds()
     */
    public function getProcessedBuilds(InstanceConfig $config, Job $job)
    {
        $jobConfig = $config->getJobConfig($job->getName());
        return $jobConfig? $jobConfig->getProcessedBuildIds() : [];
    }
}
