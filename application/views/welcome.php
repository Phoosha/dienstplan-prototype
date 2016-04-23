<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<h1>Willkommen <?php echo str_replace(' ', '&nbsp;', $name); ?>!</h1>
<div id="infoMessage"><?php echo $message; ?></div>

<h2 class="content-subhead">Ankündigungen&nbsp;<?php if ($this->ion_auth->is_admin()): ?>&nbsp;<a href="#"><i title="Erstellen" class="fa fa-plus linked-icon fa-lg" aria-hidden="true"></i></a>
<?php endif ?>
</h2>

<div class="news">
	
<?php foreach ($news as $news_item): ?>

	<section class="news-item">
		<header class="news-header">
			<h3 class="news-title"><?php echo $news_item['title']; ?></h3>
			<p class="news-meta">von <?php echo $news_item['author_full']; ?></p>
<?php if ($this->ion_auth->is_admin()): ?>
	<a href="#"><i title="Bearbeiten" class="fa fa-pencil-square-o linked-icon fa-lg" aria-hidden="true"></i></a>&nbsp;<a href="#"><i title="Löschen" class="fa fa-trash-o linked-icon fa-lg" aria-hidden="true"></i></a>
<?php endif ?>
		</header>
		<div class="news-description">
			<p><?php echo $news_item['text']; ?></p>
		</div>
	</section>
	
<?php endforeach; ?>

</div>

<h2 class="content-subhead">Wichtige Dokumente</h2>

