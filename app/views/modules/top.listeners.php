<div id="topListeners" class="<?=$class?>">
	<h3 class="label top-listeners"><?=$labels->top_listeners?></h3>
	<a href="<?=url('Listeners')?>"><?=$labels->more_listeners?></a>

	<?if ($special && $listeners):?>
	<table class="top-listeners top-3 clickable detailed" cellspacing="0" cellpadding="0">
		<tbody>
			<?foreach ($listeners as $i => $listener): if ($i > 2) break; unset($listeners[$i]);?>
			<tr title="<?=$listener->name?>">
				<th>
					<a href="<?=$listener->localUrl?>">
						<img src="<?=$listener->imgBigger?>" alt="<?=$listener->name?>" width="73" height="73" />
						<span class="position"><?=$i+1?></span>
					</a>
				</th>
				<td>
					<a href="<?=$listener->localUrl?>">
						<span class="title"><?=$listener->name?></span>
						<span class="tweets"><?=$listener->tweetsCountText?></span>
						<span class="desc"><?=$listener->descExcerpt?></span>
					</a>
				</td>
			</tr>
			<?endforeach?>
		</tbody>
	</table>
	<?endif?>

	<?if ($listeners):?>
	<table class="top-listeners top-10 clickable detailed" cellspacing="0" cellpadding="0">
		<tbody>
			<?foreach ($listeners as $i => $listener):?>
			<tr title="<?=$listener->name?>">
				<th class="position"><a href="<?=$listener->localUrl?>"><?=$i+1?></a></th>
				<td>
					<a href="<?=$listener->localUrl?>">
						<img src="<?=$listener->imgMini?>" alt="<?=$listener->name?>" width="25" />
						<span class="title"><?=$listener->name?></span>
					</a>
				</td>
			</tr>
			<?endforeach?>
		</tbody>
	</table>
	<?endif?>

</div>