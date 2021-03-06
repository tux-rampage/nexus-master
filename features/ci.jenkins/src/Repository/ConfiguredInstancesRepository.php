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

namespace Rampage\Nexus\Master\CI\Jenkins\Repository;

use Rampage\Nexus\Config\PropertyConfigInterface;
use Rampage\Nexus\Master\CI\Jenkins\Entities\InstanceConfig;
use Rampage\Nexus\Master\CI\Jenkins\BuildNotification;

/**
 * Implements the instance repository from runtime config
 */
final class ConfiguredInstancesRepository implements InstanceRepositoryInterface
{
    /**
     * @var \ReflectionProperty
     */
    private static $idPropertyReflection;

    /**
     * @var PropertyConfigInterface
     */
    private $properties;

    /**
     * @var InstanceConfig[]
     */
    private $instances = [];

    /**
     * @param PropertyConfigInterface $config
     */
    public function __construct(PropertyConfigInterface $config)
    {
        $this->properties = $config;
        $this->buildInstances();
    }

    /**
     * Check traversability
     *
     * @param unknown $var
     * @return boolean
     */
    private function isTraversable($var)
    {
        return (is_array($var) || ($var instanceof \Traversable));
    }

    /**
     * @param InstanceConfig $instance
     * @param string $id
     */
    private function injectInstanceId(InstanceConfig $instance, $id)
    {
        if (!self::$idPropertyReflection) {
            self::$idPropertyReflection = (new \ReflectionClass(InstanceConfig::class))->getProperty('id');
            self::$idPropertyReflection->setAccessible(true);
        }

        self::$idPropertyReflection->setValue($instance, $id);
    }

    /**
     * Builds the instance configs
     */
    private function buildInstances()
    {
        $instances = $this->properties->get('jenkins.instances', []);

        if (!$this->isTraversable($instances)) {
            return;
        }

        foreach ($instances as $key => $data) {
            if (!is_array($data) || !isset($data['url'])) {
                continue;
            }

            $key = (string)$key;
            $config = new InstanceConfig($data['url']);
            $this->instances[$key] = $config;
            $this->injectInstanceId($config, $key);

            if (isset($data['scanArtifactFiles'])) {
                $config->setScanArtifactFiles($data['scanArtifactFiles']);
            }

            foreach (['include', 'exclude'] as $type) {
                if (!isset($data[$type]) || !$this->isTraversable($data[$type])) {
                    continue;
                }

                foreach ($data[$type] as $name) {
                    $config->ensureJobConfig($name)->setExclude($type == 'exclude');
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\CI\Jenkins\Repository\InstanceRepositoryInterface::find()
     */
    public function find($key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\CI\Jenkins\Repository\InstanceRepositoryInterface::findAll()
     */
    public function findAll()
    {
        return $this->instances;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\CI\Jenkins\Repository\InstanceRepositoryInterface::findByBuildNotification()
     */
    public function findByBuildNotification(BuildNotification $notification)
    {
        $normalizedUrl = rtrim($notification->getJenkinsUrl(), '/');
        $matched = [];

        foreach ($this->instances as $instance) {
            if (rtrim($instance->getJenkinsUrl(), '/') == $normalizedUrl) {
                $matched[] = $instance;
            }
        }

        return $matched;
    }
}
