<div id="songTitle">
	<label><?=$page_subtitle?></label>
	<h3><?=$song->title?></h3>
</div>

<?foreach ($elements as $element):?>
<div id="song<?=$element->id?>">
	<?if (!empty($element->label)):?>
	<label><?=$element->label?></label>
	<?endif?>
	<?if (is_array($element->text)):?>
	<ul>
		<?foreach ($element->text as $item):?>
		<li><?=$item?></li>
		<?endforeach?>
	</ul>
	<?else:?>
	<p><?=$element->text?></p>
	<?endif?>
</div>
<?endforeach?>

<div class="back">
	<a href="<?=url('Schedule')?>" class="back">&laquo; <?=$this->controller->page['title']?></a>
</div>