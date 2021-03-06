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

namespace Rampage\Nexus\ODM\Mapping;

use Rampage\Nexus\Entities;
use Rampage\Nexus\ODM\Types\StreamType;
use Rampage\Nexus\ODM\Persistence\PackageDocumentRepository;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;


/**
 * Entity driver
 */
class Driver extends AbstractArrayDriver
{
    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\ODM\Mapping\AbstractArrayDriver::loadData()
     */
    protected function loadData()
    {
        $optionsAtomicSet = [
            'strategy' => ClassMetadataInfo::STORAGE_STRATEGY_ATOMIC_SET,
        ];

        return [
            Entities\Application::class => [
                'fields' => [
                    'id' => $this->identifier('string', 'NONE'),
                    'label' => $this->field('string'),
                    'icon' => $this->field(StreamType::BIN_STREAM),
                    'packages' => [
                        'type' => 'many',
                        'reference' => true,
                        'targetDocument' => Entities\ApplicationPackage::class,
                        'mappedBy' => 'name',
                        'repositoryMethod' => 'findByApplication'
                    ]
                ]
            ],

            Entities\ApplicationInstance::class => [
                'type' => self::TYPE_EMBEDDED,
                'fields' => [
                    'id' => $this->field('string'),
                    'label' => $this->field('string'),
                    'state' => $this->field('string'),
                    'application' => $this->referenceOne(Entities\Application::class),
                    'package' => $this->referenceOne(Entities\ApplicationPackage::class),
                    'previousPackage' => $this->referenceOne(Entities\ApplicationPackage::class),
                    'vhost' => $this->field('string'),
                    'path' => $this->field('string'),
                    'flavor' => $this->field('string'),
                    'userParameters' => $this->field('hash'),
                    'previousUserParameters' => $this->field('hash'),
                ]
            ],

            Entities\ApplicationPackage::class => [
                'repository' => PackageDocumentRepository::class,
                'fields' => [
                    'id' => $this->identifier('string', 'NONE'),
                    'archive' => $this->field('string'),
                    'name' => $this->field('string'),
                    'version' => $this->field('string'),
                    'isStable' => $this->field('boolean'),
                    'type' => $this->field('string'),
                    'documentRoot' => $this->field('string'),
                    'parameters' => $this->embed(Entities\PackageParameter::class, true, $optionsAtomicSet),
                    'extra' => $this->field('hash'),
                ]
            ],

            Entities\DeployTarget::class => [
                'fields' => [
                    'id' => $this->identifier(),
                    'name' => $this->field('string'),
                    'vhosts' => $this->embed(Entities\VHost::class, true, $optionsAtomicSet),
                    'defaultVhost' => $this->embed(Entities\VHost::class, false),
                    'nodes' => $this->referenceMany(Entities\Node::class, 'deployTarget'),
                    'applications' => $this->embed(Entities\ApplicationInstance::class, true, $optionsAtomicSet),
                ]
            ],

            Entities\Node::class => [
                'fields' => [
                    'id' => $this->identifier(),
                    'name' => $this->field('string'),
                    'type' => $this->field('string'),
                    'deployTarget' => $this->referenceOne(Entities\DeployTarget::class),
                    'url' => $this->field('string'),
                    'state' => $this->field('string'),
                    'applicationStates' => $this->field('hash'),
                    'publicKey' => $this->field('string'),
                    'serverInfo' => $this->field('hash'),
                ]
            ],

            Entities\PackageParameter::class => [
                'type' => self:: TYPE_EMBEDDED,
                'fields' => [
                    'name' => $this->field('string'),
                    'label' => $this->field('label'),
                    'type' => $this->field('string'),
                    'default' => $this->field('string'),
                    'options' => $this->field('hash'),
                    'valueOptions' => $this->field('hash'),
                    'required' => $this->field('boolean'),
                ]
            ],

            Entities\VHost::class => [
                'type' => self::TYPE_EMBEDDED,
                'fields' => [
                    'name' => $this->field('string'),
                    'flavor' => $this->field('string'),
                    'aliases' => $this->field('collection'),
                    'enableSsl' => $this->field('boolean')
                ]
            ]
        ];
    }
}
