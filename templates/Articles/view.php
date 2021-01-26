<h1><?= h($article->title) ?></h1>
<p><?= h($article->body) ?></p>
<p><small>Créé le : <?= $article->created->format(DATE_RFC850) ?></small></p>
<p><?= $this->Html->link('Modifier', ['action' => 'edit', $article->slug]) ?></p>
<p><b>Tags:</b> <?= h($article->tag_string) ?></p>
