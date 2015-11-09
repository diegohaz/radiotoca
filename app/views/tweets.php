<?if (isset($form)) echo $form?>

<?if (isset($listener)):?>
<div id="listenerTitle">
	<label><?=$page_subtitle?></label>
	<h3><a href="<?=$listener->localUrl?>"><img src="<?=$listener->imgMini?>" alt="<?=$listener->name?>" width="24" height="24" /> <?=$listener->name?></a></h3>
</div>

<div id="listenerOptions">
	<a href="<?=$listener->localUrl?>" class="btn profile" title="<?=$labels->view_profile?>"><span></span><?=$labels->view_profile?></a>
	<a href="<?=$listener->twitterUrl?>" class="btn twitter" title="<?=$labels->view_twitter?>" target="_blank"><span></span><?=$labels->view_twitter?></a>
</div>
<?endif?>

<?if (isset($pagination)) echo $pagination?>

<?if ($this->controller->name != 'Tweets'):?>
<div id="tweetsOptions">
	<a href="<?=$tweets[0]->listener->tweetsUrl?>" class="btn tweets" title="Ver todos os tweets de @<?=$tweets[0]->listener->screenName?>"><span></span>Ver todos os tweets de @<?=$tweets[0]->listener->screenName?></a>
</div>
<?endif?>

<div id="tweetList" class="sections">
	<?foreach ($tweets as $tweet):?>
	<div class="tweet section">
		<a href="<?=$tweet->listener->localUrl?>"><img src="<?=$tweet->listener->imgBigger?>" alt="<?=$tweet->listener->name?>" width="73" height="73" /></a>
		<div class="status">
			<p class="message"><?=$tweet->text?></p>
			<p class="date"><?=$tweet->footer?></p>
		</div>
		<?if (!isset($listener) && $this->controller->name == 'Tweets'):?>
		<div class="options">
			<ul>
				<li><a href="<?=$tweet->listener->twitterUrl?>" class="twitter" title="<?=$labels->view_twitter?>" target="_blank"><span></span><?=$labels->view_twitter?></a></li>
				<li><a href="<?=$tweet->listener->tweetsUrl?>" class="tweets" title="<?=$labels->more_from_user?>"><span></span><?=$labels->more_from_user?></a></li>
			</ul>
		</div>
		<?endif?>
	</div>
	<?endforeach?>
</div>

<?if (isset($pagination)) echo $pagination?>

<?if (isset($listener)):?>
<div class="back">
	<a href="<?=url('Tweets')?>" class="back">&laquo; <?=$this->controller->page['title']?></a>
</div>
<?endif?>
