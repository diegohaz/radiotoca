<?if (isset($listener)):?>
<div id="listenerTitle">
	<label><?=$page_subtitle?></label>
	<h3><?=$listener->name?></h3>
</div>

<div id="listenerOptions">
	<a href="<?=$listener->tweetsUrl?>" class="btn tweets" title="<?=$labels->more_from_user?>"><span></span><?=$labels->more_from_user?></a>
	<a href="<?=$listener->twitterUrl?>" class="btn twitter" title="<?=$labels->view_twitter?>" target="_blank"><span></span><?=$labels->view_twitter?></a>
</div>

<div id="listenerProfile">
	<img src="<?=$listener->imgProfile?>" alt="<?=$listener->name?>" width="128" height="128" />

	<?foreach ($listener->profile as $info):?>
	<div id="listener<?=$info->id?>">

		<?if (!empty($info->label)):?>
		<label><?=$info->label?></label>
		<?endif?>

		<?if ($info->id == 'LastTweets'):?>
		<?=$info->value?>
		<?else:?>
		<p><?=$info->value?></p>
		<?endif?>
	</div>
	<?endforeach?>
</div>
<?endif?>

<?if (isset($pagination)) echo $pagination?>

<?if (isset($listeners)):?>
<table class="top-listeners top-3 clickable detailed" cellspacing="0" cellpadding="0">
	<tbody>
		<?foreach ($listeners as $_listener):?>
		<tr title="<?=$_listener->name?>">
			<th>
				<a href="<?=$_listener->localUrl?>">
					<img src="<?=$_listener->imgBigger?>" alt="<?=$_listener->name?>" width="73" height="73" />
				</a>
			</th>
			<td>
				<a href="<?=$_listener->localUrl?>">
					<span class="title"><?=$_listener->name?></span>
					<span class="tweets"><?=$_listener->tweetsCountText?></span>
					<span class="desc"><?=$_listener->desc?></span>
				</a>
			</td>
		</tr>
		<?endforeach?>
	</tbody>
</table>
<?endif?>

<?if (isset($pagination)) echo $pagination?>

<?if (isset($listener)):?>
<div class="back">
	<a href="<?=url('Listeners')?>" class="back">&laquo; <?=$this->controller->page['title']?></a>
</div>
<?endif?>
