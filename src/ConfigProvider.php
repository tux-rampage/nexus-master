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

namespace Rampage\Nexus\Master;

use Rampage\Nexus\Config\AbstractConfigProvider;
use Rampage\Nexus\Config\CommonConfigProvider;
use Rampage\Nexus\Config\PhpDirectoryProvider;

use Rampage\Nexus\ODM\ConfigProvider as ODMConfigProvider;


/**
 * Config provider for deployment master apps
 */
class ConfigProvider extends AbstractConfigProvider
{
    /**
     * @var array
     */
    private $features = [
        ODMConfigProvider::class
    ];

    /**
     * @param array $features
     */
    public function __construct(array $features = null)
    {
        if ($features !== null) {
            $this->features = array_values($features);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Config\AbstractConfigProvider::getGeneratedFilePath()
     */
    protected function getGeneratedFilePath()
    {
        return __DIR__ . '/../_generated/config.php';
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Config\AbstractConfigProvider::getProviders()
     */
    protected function getProviders()
    {
        $providers = $this->features;

        array_splice($providers, 0, 0, [
            CommonConfigProvider::class,
            new PhpDirectoryProvider(__DIR__ . '/../config')
        ]);

        return $providers;
    }
}
