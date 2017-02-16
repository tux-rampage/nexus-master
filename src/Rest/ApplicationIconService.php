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

use Psr\Http\Message\ServerRequestInterface;

use Zend\Diactoros\Stream;
use Zend\Diactoros\Response;


/**
 * App Icon action
 */
class ApplicationIconService
{
    /**
     * @var ApplicationsService
     */
    private $parent;

    /**
     * @param ApplicationsService $repository
     */
    public function __construct(ApplicationsService $parent)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function get(ServerRequestInterface $request)
    {
        /** @var \Rampage\Nexus\Entities\Application $application */
        $application = $this->parent->get($request);

        if (!$application) {
            return null;
        }

        $icon = $application->getIcon();

        if (!$icon) {
            $icon = new Stream(__DIR__ . '/../../resources/images/default-app-icon.png');
        }

        return new Response($icon, 200, [
            'Content-Type' => 'image/png'
        ]);
    }
}
