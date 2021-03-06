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

namespace MeCms\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Validation\PhotosAlbumValidator;
use MeCms\ORM\Query;

/**
 * PhotosAlbums model
 */
class PhotosAlbumsTable extends AppTable
{
    /**
     * Cache configuration name
     * @var string
     */
    protected $cache = 'photos';

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return void
     */
    public function afterDelete(Event $event, EntityInterface $entity): void
    {
        @rmdir($entity->get('path'));

        parent::afterDelete($event, $entity);
    }

    /**
     * Called after an entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity): void
    {
        @mkdir($entity->get('path'), 0777, true);

        parent::afterSave($event, $entity);
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules->add($rules->isUnique(['slug'], I18N_VALUE_ALREADY_USED))
            ->add($rules->isUnique(['title'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * "active" find method
     * @param \Cake\ORM\Query $query Query object
     * @return \Cake\ORM\Query Query object
     */
    public function findActive(Query $query): Query
    {
        return $query->innerJoinWith($this->Photos->getAlias(), function (Query $query) {
            return $query->find('active');
        })->distinct();
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('photos_albums');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->hasMany('Photos', ['className' => 'MeCms.Photos'])
            ->setForeignKey('album_id');

        $this->addBehavior('Timestamp');

        $this->_validatorClass = PhotosAlbumValidator::class;
    }
}
