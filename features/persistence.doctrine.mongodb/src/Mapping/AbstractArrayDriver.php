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

use Rampage\Nexus\Exception\InvalidArgumentException;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver as MappingDriverInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;

use Zend\Stdlib\Parameters;

/**
 * An abstract array driver
 */
abstract class AbstractArrayDriver implements MappingDriverInterface
{
    const TYPE_SUPERCLASS = 'superclass';
    const TYPE_EMBEDDED = 'embedded';

    /**
     * @var array
     */
    protected $classes = [];

    /**
     * Load all class information as array
     *
     * @return array
     */
    abstract protected function loadData();

    /**
     * Initialize
     */
    protected function init()
    {
        if (!$this->classes) {
            $this->classes = $this->loadData();
        }
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver::getAllClassNames()
     */
    public function getAllClassNames()
    {
        $this->init();
        return array_keys($this->classes);
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver::isTransient()
     */
    public function isTransient($className)
    {
        $this->init();
        return !isset($this->classes[$className]);
    }

    /**
     * @param string $className
     * @throws InvalidArgumentException
     * @return Parameters
     */
    protected function getClassData($className)
    {
        $this->init();

        if (!isset($this->classes[$className])) {
            throw new InvalidArgumentException('Could not get mapping data for ' . $className);
        }

        return new Parameters($this->classes[$className]);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @param string $name
     * @param array $mapping
     */
    protected function mapField(ClassMetadataInfo $metadata, $name, array $mapping)
    {
        $mapping['fieldName'] = $name;
        $metadata->mapField($mapping);
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @param string $name
     * @param array $index
     */
    protected function addIndex(ClassMetadataInfo $metadata, $name, array $index)
    {
        $options = (isset($index['options']) && is_array($index['options']))? $index['options'] : [];
        $keys = $index['keys'];

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        $metadata->addIndex($keys, $options);
    }

    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver::loadMetadataForClass()
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        /** @var \Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo $metadata */
        $info = $this->getClassData($className);

        switch ($info->get('type')) {
            case self::TYPE_EMBEDDED:
                $metadata->isEmbeddedDocument = true;
                break;

            case self::TYPE_SUPERCLASS:
                $metadata->isMappedSuperclass = true;
                // Break intentionally omitted

            default:
                $repoClass = $info->get('repository');

                if ($repoClass) {
                    $metadata->setCustomRepositoryClass($repoClass);
                }
        }

        $metadata->setCollection($info->get('collection', str_replace('\\', '.', $className)));

        foreach ($info->get('fields', []) as $name => $field) {
            $this->mapField($metadata, $name, $field);
        }

        foreach ($info->get('indexes', []) as $name => $index) {
            $this->addIndex($metadata, $name, $index);
        }
    }

    /**
     * Create a reference mapping
     *
     * @param string $type
     * @param unknown $targetDocument
     * @param unknown $mappedBy
     * @return boolean[]|string[]|unknown[]
     */
    protected function ref($type, $targetDocument, $mappedBy = null, array $options = [])
    {
        // Defaults:
        $options = array_merge([
            'storeAs' => ClassMetadataInfo::REFERENCE_STORE_AS_DB_REF_WITH_DB,
        ], $options);

        return array_merge($options, [
            'reference' => true,
            'type' => $type,
            'targetDocument' => $targetDocument,
            'mappedBy' => $mappedBy,
        ]);
    }

    /**
     * Create a n:1 reference
     *
     * @param string $targetDocument
     * @param string $mappedBy
     * @return array
     */
    protected function referenceOne($targetDocument, $mappedBy = null, array $options = [])
    {
        return $this->ref('one', $targetDocument, $mappedBy, $options);
    }

    /**
     * Create a *:n reference
     *
     * @param string $targetDocument
     * @param string $mappedBy
     * @return array
     */
    protected function referenceMany($targetDocument, $mappedBy = null, array $options = [])
    {
        $options = array_merge((array)(new ReferenceMany()), $options);
        return $this->ref('many', $targetDocument, $mappedBy, $options);
    }

    /**
     * Create a embed mapping
     *
     * @param string $targetDocument
     * @param string $mappedBy
     * @return boolean[]|string[]|unknown[]
     */
    protected function embed($targetDocument, $many = false, array $options = [])
    {
        return array_merge($options, [
            'embedded' => true,
            'type' => $many? 'many' : 'one',
            'targetDocument' => $targetDocument,
        ]);
    }

    /**
     * Create an identifier mapping
     *
     * @param string $type
     * @param string $strategy
     * @return boolean[]|string[]
     */
    protected function identifier($type = null, $strategy = 'AUTO')
    {
        return $this->field($type, [
            'id' => true,
            'strategy' => $strategy
        ]);
    }

    /**
     * Create a generic field mapping
     *
     * @param   string  $type       The field type
     * @param   array   $options    Additional mapping options
     * @return  array
     */
    protected function field($type = null, array $options = [])
    {
        $options['type'] = $type;
        return $options;
    }
}
