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

namespace Rampage\Nexus\Master\Action\NodeApi;

use Rampage\Nexus\Repository\NodeRepositoryInterface;
use Rampage\Nexus\Master\Rest\Node\NodeContextTrait;
use Rampage\Nexus\Archive\ArchiveLoaderInterface;

use Psr\Http\Message\ServerRequestInterface;

use Zend\Diactoros\Stream;


/**
 * Implements the package service contract
 */
class PackageService
{
    use NodeContextTrait;

    /**
     * @var ArchiveLoaderInterface
     */
    private $archiveLoader;

    /**
     * @param NodeRepositoryInterface $repository
     */
    public function __construct(ArchiveLoaderInterface $archiveLoader)
    {
        $this->archiveLoader = $archiveLoader;
    }

    /**
     * Returns a package stream
     *
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\StreamInterface|null
     */
    public function get(ServerRequestInterface $request)
    {
        $node = $this->ensureNodeContext($request);
        $applicationId = $request->getAttribute('applicationId');

        if (!$applicationId) {
            return null;
        }

        $application = $node->getDeployTarget()->findApplicationInstance($applicationId);
        $package = $application? $application->getPackage() : null;
        $archive = $package->getArchive();

        if (!$archive) {
            return null;
        }

        $file = $this->archiveLoader->ensureLocalArchiveFile($archive);
        return new Stream($file->getPathname());
    }
}
