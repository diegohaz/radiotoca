<div id="dayTitle">
	<label><?=$page_subtitle?></label>
	<h3><?=$day->title?></h3>
</div>

<div id="dayNavigation">
	<a href="<?=$prev->url?>" class="btn white">◄ <?=$prev->title?></a>
	<a href="<?=$next->url?>" class="btn white"><?=$next->title?> ►</a>
</div>

<table class="schedule detailed clickable" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<?foreach ($day->programs as $program):?>
		<tr title="<?=$program->title?>, <?=$program->fullHour?>">
			<th class="hour"><a href="<?=$program->url?>"><?=$program->fullHour?></th>
			<td>
				<a href="<?=$program->url?>">
					<span class="title"><?=$program->title?></span>
					<span class="genre"><?=$program->genre?></span>
					<span class="desc"><?=$program->desc?></span>
				</a>
			</td>
		</tr>
		<?endforeach?>
	<tbody>
</table>

<div class="back">
	<a href="<?=url('Schedule')?>" class="back">&laquo; <?=$this->controller->page['title']?></a>
</div>