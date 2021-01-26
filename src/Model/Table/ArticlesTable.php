<?php
namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\ORM\Table;
// la classe Text
use Cake\Utility\Text;
// la classe EventInterface
use Cake\Event\EventInterface;
// la classe Query
use Cake\ORM\Query;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        $this->belongsToMany('Tags', [
            'joinTable' => 'articles_tags',
            'dependent' => true
        ]);
    }

    public function beforeSave($event, $entity, $options)
    {

        if ($entity->tag_string) {
            $entity->tags = $this->_buildTags($entity->tag_string);
        }

        if ($entity->isNew() && !$entity->slug) {
            $sluggedTitle = Text::slug($entity->title);
            // On ne garde que le nombre de caractère correspondant à la longueur
            // maximum définie dans notre schéma
            $entity->slug = substr($sluggedTitle, 0, 191);
        }
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('title')
            ->minLength('title', 3)
            ->maxLength('title', 255)

            ->notEmptyString('body')
            ->minLength('body', 10);

        return $validator;
    }

    // Le tableau $options va contenir l'option 'tags' que nous avons passé
    // à find('tagged') dans notre action de controller.
    public function findTagged(Query $query, array $options)
    {
        $columns = [
            'Articles.id', 'Articles.user_id', 'Articles.title',
            'Articles.body', 'Articles.published', 'Articles.created',
            'Articles.slug',
        ];

        $query = $query
            ->select($columns)
            ->distinct($columns);

        if (empty($options['tags'])) {
            // si aucun tag n'est fourni, trouvons les articles qui n'ont pas de tags
            $query->leftJoinWith('Tags')
                ->where(['Tags.title IS' => null]);
        } else {
            // Trouvons les articles qui ont au moins un des tags fourni
            $query->innerJoinWith('Tags')
                ->where(['Tags.title IN' => $options['tags']]);
        }

        return $query->group(['Articles.id']);
    }

    protected function _buildTags($tagString)
    {
        // Trim des tags
        $newTags = array_map('trim', explode(',', $tagString));
        // Retire les tags vides
        $newTags = array_filter($newTags);
        // Dé-doublonne les tags
        $newTags = array_unique($newTags);

        $out = [];
        $query = $this->Tags->find()
            ->where(['Tags.title IN' => $newTags])
            ->all();

        // Retire les tags existant de la liste des nouveaux tags.
        foreach ($query->extract('title') as $existing) {
            $index = array_search($existing, $newTags);
            if ($index !== false) {
                unset($newTags[$index]);
            }
        }
        // Ajout des tags existant.
        foreach ($query as $tag) {
            $out[] = $tag;
        }
        // Ajout des nouveaux tags.
        foreach ($newTags as $tag) {
            $out[] = $this->Tags->newEntity(['title' => $tag]);
        }
        return $out;
    }

}
