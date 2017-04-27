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

namespace Rampage\Nexus\Master\Rest\DeployTarget;

use Psr\Http\Message\ServerRequestInterface;
use Rampage\Nexus\Entities\DeployTarget;
use Zend\Diactoros\Response\EmptyResponse;
use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\Repository\ApplicationRepositoryInterface;
use Rampage\Nexus\Master\Rest\DeployTargetService;
use Rampage\Nexus\Repository\PersistenceManagerInterface;
use Zend\Stdlib\Parameters;


/**
 * Implements the service for applications
 */
class ApplicationsService extends AbstractService
{
    /**
     * @var ApplicationRepositoryInterface
     */
    private $applicationRepository;

    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\Rest\DeployTarget\AbstractService::__construct()
     */
    public function __construct(
        DeployTargetService $context,
        PersistenceManagerInterface $persistenceManager,
        PackageRepositoryInterface $packageRepository,
        ApplicationRepositoryInterface $applicationRepository
        )
    {
        parent::__construct($context, $persistenceManager);

        $this->packageRepository = $packageRepository;
        $this->applicationRepository = $applicationRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $allowList
     * @return NULL|\Rampage\Nexus\Entities\NULL|\Rampage\Nexus\Entities\ApplicationInstance
     */
    public function get(ServerRequestInterface $request, DeployTarget $target = null, $allowList = true)
    {
        $target = $target?: $this->context->get($request);
        $id = $request->getAttribute('applicationId');

        if (!$target || (!$allowList && !$id)) {
            return null;
        }

        return ($id)? $target->findApplicationInstance($id) : $target->getApplications();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Master\Rest\DeployTarget\AbstractService::save()
     */
    protected function save($entity)
    {
        parent::save($entity);

        if (($entity instanceof DeployTarget) && $entity->canSync()) {
            $entity->sync();
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return NULL|\Zend\Diactoros\Response\EmptyResponse
     */
    public function delete(ServerRequestInterface $request)
    {
        $target = $this->context->get($request);
        $app = $this->get($request, $target, false);

        if (!$app) {
            return null;
        }

        $target->removeApplication($app);
        $this->save($target);

        return $app;
    }

    /**
     * @param ServerRequestInterface $request
     * @return NULL
     */
    public function put(ServerRequestInterface $request)
    {
        $target = $this->context->get($request);
        $app = $this->get($request, $target, false);

        if (!$app) {
            return null;
        }

        $app->exchangeArray($request->getParsedBody());
        $this->save($target);

        return $app;
    }

    /**
     * Create a new application instance
     *
     * @param ServerRequestInterface $request
     * @return ApplicationInstance|null
     */
    public function post(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
        $params = new Parameters($data);
        $target = $this->context->get($request);
        $package = $this->packageRepository->findOne($params->get('request'));

        if (!$target || !$package) {
            return null;
        }

        $application = $this->applicationRepository->findOne($package->getName());
        $vhost = $target->getVHost($params->get('vhost'));
        $path = $params->get('path');
        $instance = new ApplicationInstance($application, $params->get('id'), $vhost, $path);

        unset($data['package']);
        $instance->exchangeArray($data);

        $target->addApplication($instance);
        $this->save($target);

        return $application;
    }
}
