<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\View\Cell;

use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\View\Cell;

/**
 * PagesWidgets cell
 */
class PagesWidgetsCell extends Cell
{
    /**
     * Constructor
     * @param \Cake\Network\Request $request The request to use in the cell
     * @param \Cake\Network\Response $response The request to use in the cell
     * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
     * @param array $cellOptions Cell options to apply
     * @return void
     * @uses Cake\View\Cell::__construct()
     */
    public function __construct(
        Request $request = null,
        Response $response = null,
        EventManager $eventManager = null,
        array $cellOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $cellOptions);

        $this->loadModel('MeCms.Pages');
    }

    /**
     * Categories widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function categories($render = 'form')
    {
        $this->viewBuilder()->setTemplate(sprintf('categories_as_%s', $render));

        //Returns on categories index
        if ($this->request->isUrl(['_name' => 'pagesCategories'])) {
            return;
        }

        $categories = $this->Pages->Categories->find('active')
            ->select(['title', 'slug', 'page_count'])
            ->order([sprintf('%s.title', $this->Pages->Categories->getAlias()) => 'ASC'])
            ->formatResults(function ($results) {
                return $results->indexBy('slug');
            })
            ->cache('widget_categories', $this->Pages->cache);

        $this->set(compact('categories'));
    }

    /**
     * Pages list widget
     * @return void
     */
    public function pages()
    {
        //Returns on pages index
        if ($this->request->isUrl(['_name' => 'pagesCategories'])) {
            return;
        }

        $pages = $this->Pages->find('active')
            ->select(['title', 'slug'])
            ->order([sprintf('%s.title', $this->Pages->getAlias()) => 'ASC'])
            ->cache(sprintf('widget_list'), $this->Pages->cache);

        $this->set(compact('pages'));
    }
}
