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

namespace Rampage\Nexus\Master\Rest;

use Rampage\Nexus\Package\PackageInterface;
use Rampage\Nexus\Package\ZpkPackage;

use Rampage\Nexus\Exception\Http\BadRequestException;
use Rampage\Nexus\Exception\RuntimeException;

use Rampage\Nexus\Entities\ApplicationPackage;
use Rampage\Nexus\Archive\ArchiveLoaderInterface;

use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\Repository\RestService\GetableTrait;
use Rampage\Nexus\Repository\RestService\DeletableTrait;
use Rampage\Nexus\Repository\PersistenceManagerInterface;

use Psr\Http\Message\ServerRequestInterface;

use Zend\Http\Header\ContentType;


/**
 * Implements the packages service
 */
class PackagesService
{
    use GetableTrait;
    use DeletableTrait;

    /**
     * @var ArchiveLoaderInterface
     */
    private $archiveLoader;

    /**
     * @var array
     */
    private $mimeToArchiveTypeMap = [];

    /**
     * @param PackageRepositoryInterface $repository
     */
    public function __construct(PackageRepositoryInterface $repository, PersistenceManagerInterface $persistenceManager)
    {
        $this->repository = $repository;
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @throws BadRequestException
     * @return string
     */
    private function getMimeType(ServerRequestInterface $request)
    {
        $contentType = ContentType::fromString('Content-Type: ' . $request->getHeaderLine('Content-Type'));
        $mimeType = $contentType->match('application/zip');

        if ($mimeType === false) {
            throw new BadRequestException('Invalid content-type');
        }

        return $mimeType;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $name
     */
    private function savePackageFile(ServerRequestInterface $request, $target)
    {
        $handle = fopen($target, 'w');

        if (!$handle) {
            throw new RuntimeException('Failed to open temporary package file');
        }

        $size = stream_copy_to_stream($request->getBody()->detach(), $handle);
        fclose($handle);

        if (!$size) {
            throw new RuntimeException('Failed to write package contents to disk');
        }
    }

    /**
     * @return string
     */
    private function buildFileName()
    {
        return tempnam($this->archiveLoader->getDownloadDirectory(), 'package-');
    }

    /**
     * @param ServerRequestInterface $request
     * @return PackageInterface
     */
    private function loadPackage(ServerRequestInterface $request)
    {
        $mimeType = $this->getMimeType($request);
        $tempFile = $this->buildFileName();
        $archiveType = \Phar::ZIP;

        if (isset($this->mimeToArchiveTypeMap[$mimeType])) {
            $archiveType = $this->mimeToArchiveTypeMap[$mimeType];
        }

        $this->savePackageFile($request, $tempFile);

        try {
            $archive = new \PharData($tempFile, null, null, $archiveType);
            $package = $this->archiveLoader->getPackage($archive);
        } catch (\Exception $e) {
            unlink($tempFile);
            throw new BadRequestException('Invalid archive');
        }

        return $package;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function put(ServerRequestInterface $request)
    {
        /** @var ApplicationPackage $entity */
        $package = $this->loadPackage($request);
        $tempFile = $package->getArchive();

        try {
            $entity = $this->repository->findOne($package->getId());

            if (!$entity) {
                $entity = new ApplicationPackage($package);
            }

            $package = null;
            $suffix = ($entity->getType() == ZpkPackage::TYPE_ZPK)? '.zpk' : '.zip';
            $finalName = $entity->getId() . $suffix;

            if (file_exists($finalName) && !unlink($finalName)) {
                throw new RuntimeException('Failed to replace existing package');
            }

            rename($tempFile, $this->archiveLoader->getDownloadDirectory() . '/' . $finalName);

            $this->persistenceManager->persist($entity);
            $this->persistenceManager->flush($entity);
        } catch (\Exception $e) {
            @unlink($tempFile);
            throw $e;
        }

        return $entity;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function post(ServerRequestInterface $request)
    {
        $package = $this->loadPackage($request);
        $tempFile = $package->getArchive();
        $entity = new ApplicationPackage($package);
        $package = null;
        $suffix = ($entity->getType() == ZpkPackage::TYPE_ZPK)? '.zpk' : '.zip';
        $finalName = $entity->getId() . $suffix;

        try {
            if (file_exists($finalName)) {
                throw new BadRequestException('The package file already exists');
            }

            $entity->setArchive($finalName);
            rename($tempFile, $this->archiveLoader->getDownloadDirectory() . '/' . $finalName);

            $this->persistenceManager->persist($entity);
            $this->persistenceManager->flush($entity);
        } catch (\Exception $e) {
            @unlink($tempFile);
            throw $e;
        }

        return $entity;
    }
}
