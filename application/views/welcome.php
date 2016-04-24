<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h1>Willkommen <?php echo str_replace(' ', '&nbsp;', $name); ?>!</h1>
<div id="infoMessage"><?php echo $message; ?></div>

<h2 class="content-subhead">Ankündigungen<?php if ($this->ion_auth->is_admin()): ?>&nbsp;<button title="Erstellen" class="pure-button secondary-button icon-button"><i class="fa-fw fa fa-plus" aria-hidden="true"></i></button></a>
<?php endif ?>
</h2>

<div class="news">
	
<?php foreach ($news as $news_item): ?>

	<section class="news-item">
		<header class="news-header">
			<h3 class="news-title"><?php echo $news_item['title']; ?></h3>
			<p class="news-meta">von <?php echo $news_item['author_full']; ?></p>
<?php if ($this->ion_auth->is_admin()): ?>
	<a href="<?php echo current_url() ?>#"><button title="Bearbeiten" class="pure-button secondary-button icon-button"><i class="fa-fw fa fa-pencil-square-o" aria-hidden="true"></i></button></a><a href="<?php echo current_url() ?>#"><button title="Löschen" class="pure-button secondary-button icon-button danger-button"><i class="fa-fw fa fa-trash-o" aria-hidden="true"></i></button></a>
<?php endif ?>
		</header>
		<div class="news-description">
			<p><?php echo $news_item['text']; ?></p>
		</div>
	</section>
	
<?php endforeach; ?>

</div>

<h2 class="content-subhead">Wichtige Dokumente</h2>

