<?foreach ($days as $day):?>
<div>
	<h3><a href="<?=$day->url?>"><?=$day->title?></a></h3>

	<table class="schedule clickable" cellpadding="0" cellspacing="0">
		<tbody>
			<?foreach ($day->programs as $program):?>
			<tr title="<?=$program->title?>, <?=$program->day?>, <?=$program->fullHour?>">
				<th class="hour"><a href="<?=$program->url?>"><?=$program->hour?></th>
				<td class="title"><a href="<?=$program->url?>"><?=$program->title?></a></td>
			</tr>
			<?endforeach?>
		<tbody>
	</table>

</div>
<?endforeach?>