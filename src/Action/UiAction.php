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

namespace Rampage\Nexus\Master\Action;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\Middleware\MiddlewareInterface;
use Interop\Http\Middleware\DelegateInterface;

use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;


/**
 * Renders the user interface page
 */
class UiAction implements MiddlewareInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var string
     */
    private $excludePaths = [
        '/assets'
    ];

    /**
     * @param TemplateRendererInterface $renderer
     */
    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     * @see \Interop\Http\Middleware\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $path = $request->getUri()->getPath();

        foreach ($this->excludePaths as $exclude) {
            if (strpos($path, $exclude) === 0) {
                return $delegate->process($request);
            }
        }

        return new HtmlResponse($this->renderer->render('ui::index'));
    }
}
