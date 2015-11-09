<div id="iTwitter">
	<p class="label itwitter"><?=$labels->itwitter?></p>
	<a href="<?=url('Tweets')?>"><?=$labels->more_tweets?></a>

	<?if (isset($form)) echo $form?>

	<div id="tweets">
		<?foreach ($tweets as $tweet):?>
		<div class="tweet">
			<div class="arrow"></div>
			<div class="wrapper">
				<div class="img">
					<a href="<?=$tweet->listener->localUrl?>" title="<?=$tweet->listener->name?>"><img src="<?=$tweet->listener->imgBigger?>" width="100%" alt="<?=$tweet->listener->name?>" /></a>
				</div>
				<div class="status">
					<p class="message"><?=$tweet->text?></p>
					<p class="date"><a href="<?=$tweet->url?>" target="_blank"><?=$tweet->date?></a></p>
				</div>
			</div>
			<div class="options">
				<ul>
					<li><a href="<?=$tweet->listener->twitterUrl?>" class="twitter" title="<?=$labels->view_twitter?>" target="_blank"><?=$labels->view_twitter?></a></li>
					<li><a href="<?=$tweet->listener->tweetsUrl?>" class="tweets" title="<?=$labels->more_from_user?>"><?=$labels->more_from_user?></a></li>
				</ul>
				<div class="border"></div>
			</div>
		</div>
		<?endforeach?>
	</div>
</div>