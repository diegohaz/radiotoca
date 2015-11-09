<div id="iSchedule">
	<ul>
		<?foreach ($elements as $schedule):?>
		<li class="<?=$schedule->class?>">
			<p><span><?=$schedule->label?></span> <?=$schedule->hour?></p>
			<h3><?=$schedule->title?></h3>
		</li>
		<?endforeach?>
	</ul>
</div>