<?php
declare(strict_types=1);
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace MeCms\Controller;

use Cake\Event\Event;
use Cake\Http\Response;
use Cake\ORM\Entity;
use MeCms\Controller\AppController;
use MeCms\Utility\StaticPage;

/**
 * Pages controller
 * @property \MeCms\Model\Table\PagesTable $Pages
 */
class PagesController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     */
    public function beforeFilter(Event $event): void
    {
        parent::beforeFilter($event);

        $this->Auth->deny('preview');
    }

    /**
     * Views page.
     *
     * It first checks if there's a static page, using all the passed
     *  arguments.
     * Otherwise, it checks for the page in the database, using that slug.
     *
     * Static pages must be located in `APP/View/StaticPages/`.
     * @param string $slug Page slug
     * @return \Cake\Http\Response|null|void
     * @uses \MeCms\Utility\StaticPage::get()
     * @uses \MeCms\Utility\StaticPage::title()
     */
    public function view(string $slug)
    {
        //Checks if there exists a static page
        $static = StaticPage::get($slug);

        if ($static) {
            $page = new Entity(array_merge([
                'category' => new Entity(['slug' => null, 'title' => null]),
                'title' => StaticPage::title($slug),
                'subtitle' => null,
            ], compact('slug')));

            $this->set(compact('page'));

            return $this->render($static);
        }

        $slug = rtrim($slug, '/');
        $page = $this->Pages->findActiveBySlug($slug)
            ->contain([$this->Pages->Categories->getAlias() => ['fields' => ['title', 'slug']]])
            ->cache(sprintf('view_%s', md5($slug)), $this->Pages->getCacheName())
            ->firstOrFail();

        $this->set(compact('page'));
    }

    /**
     * Preview for pages.
     * It uses the `view` template.
     * @param string $slug Page slug
     * @return \Cake\Http\Response
     */
    public function preview(string $slug): Response
    {
        $page = $this->Pages->findPendingBySlug($slug)
            ->contain([$this->Pages->Categories->getAlias() => ['fields' => ['title', 'slug']]])
            ->firstOrFail();

        $this->set(compact('page'));

        return $this->render('view');
    }
}
