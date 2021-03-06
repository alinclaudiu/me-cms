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
$isView = $this->getRequest()->isAction('view', 'Posts') && !$this->getRequest()->isAjax();
$category = $post->get('category');
$user = $post->get('user');
?>

<article class="clearfix mb-4">
    <header class="mb-3 media">
        <?php
        if (getConfig('post.author_picture')) {
            echo $this->Thumb->fit($user->get('picture'), ['width' => 100], [
                'class' => 'd-none d-sm-block mr-3 rounded-circle user-picture',
                'title' => __d('me_cms', 'Posted by {0}', $user->get('full_name')),
            ]);
        }
        ?>

        <div class="media-body">
            <?php if (getConfig('post.category') && $category) : ?>
                <h5 class="category mb-2">
                    <?= $this->Html->link($category->get('title'), $category->get('url')) ?>
                </h5>
            <?php endif; ?>

            <h2 class="title mb-2">
                <?= $this->Html->link($post->get('title'), $post->get('url')) ?>
            </h2>

            <?php if ($post->has('subtitle')) : ?>
                <h4 class="subtitle mb-2">
                    <?= $this->Html->link($post->get('subtitle'), $post->get('url')) ?>
                </h4>
            <?php endif; ?>

            <div class="info">
                <?php
                if (getConfig('post.author')) {
                    echo $this->Html->div('author', __d('me_cms', 'Posted by {0}', $user->get('full_name')), ['icon' => 'user']);
                }

                $created = $post->get('created');
                if (getConfig('post.created')) {
                    echo $this->Html->div('created', $this->Html->time(
                        __d('me_cms', 'Posted on {0}', $created->i18nFormat()),
                        ['class' => 'date', 'icon' => 'clock']
                    ));
                }

                $modified = $post->get('modified');
                if (getConfig('post.modified') && $modified != $created) {
                    echo $this->Html->div('modified small', $this->Html->time(
                        __d('me_cms', 'Updated on {0}', $modified->i18nFormat()),
                        ['class' => 'date', 'icon' => 'clock']
                    ));
                }
                ?>
            </div>
        </div>
    </header>

    <main class="text-justify">
        <?php
        //Truncates the text when necessary. The text will be truncated to the
        //  location of the `<!-- readmore -->` tag. If the tag is not present,
        //  the value in the configuration will be used
        $text = $post->get('text');
        if (!$this->getRequest()->isAction(['view', 'preview'])) {
            $strpos = strpos($text, '<!-- read-more -->');
            $truncatedOptions = ['ellipsis' => ''];
            if (!$strpos) {
                $strpos = getConfigOrFail('default.truncate_to');
                $truncatedOptions = ['html' => true];
            }
            $text = $truncatedText = $this->Text->truncate($text, $strpos, $truncatedOptions);
        }
        echo $text;
        ?>
    </main>

    <?php if (getConfig('post.tags')) : ?>
        <div class="tags mt-2">
            <?php foreach ($post->get('tags') as $tag) : ?>
                <?= $this->Html->link($tag->get('tag'), $tag->get('url'), ['icon' => 'tags']) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($truncatedText) && $truncatedText !== $text) : ?>
    <div class="buttons mt-2 text-right">
        <?= $this->Html->button(__d('me_cms', 'Read more'), $post->get('url'), ['class' => ' readmore']) ?>
    </div>
    <?php endif; ?>

    <?php if (getConfig('post.shareaholic') && $isView) : ?>
    <div class="mt-3">
        <?= $this->Html->shareaholic(getConfigOrFail('shareaholic.app_id')) ?>
    </div>
    <?php endif; ?>

    <?php if (getConfig('disqus.shortname') && getConfig('post.enable_comments') && $post->get('enable_comments') && $isView) : ?>
    <div id="disqus_thread" class="mt-3"></div>
    <script>
    /*
    var disqus_config = function () {
    this.page.url = '<?= $post->get('url') ?>';
    this.page.identifier = '<?= sprintf('post-#%s', $post->get('id')) ?>';
    };
    */
    (function() {
    var d = document, s = d.createElement('script');
    s.src = 'https://<?= getConfig('disqus.shortname') ?>.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
    })();
    </script>
    <?php endif; ?>
</article>
