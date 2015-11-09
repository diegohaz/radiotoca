<div id="topSongs" class="<?=$class?>">
	<h3 class="label top-songs"><?=$labels->top_songs?></h3>

	<table class="top-songs clickable detailed" cellspacing="0" cellpadding="0">
		<tbody>
			<?foreach ($songs as $i => $song):?>
			<tr title="<?=$song->label?>">
				<th class="position"><a href="<?=$song->url?>"><?=$i+1?></a></th>
				<td class="music">
					<a href="<?=$song->url?>">
						<span class="title"><?=$song->title?></span>
						<span class="artist"><?=$song->artist?></span>
					</a>
				</td>
			</tr>
			<?endforeach?>
		</tbody>
	</table>

</div>