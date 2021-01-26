<h1>Ajouter un article</h1>
<?php
echo $this->Form->create($article);
// Hard code the user for now.
echo $this->Form->control('user_id', ['type' => 'hidden', 'value' => 1]);
echo $this->Form->control('title');
echo $this->Form->control('body', ['rows' => '3']);
echo $this->Form->button(__('Sauvegarder l\'article'));
echo $this->Form->end();
//echo $this->Form->control('tags._ids', ['options' => $tags]);
echo $this->Form->control('tag_string', ['type' => 'text']);
?>
