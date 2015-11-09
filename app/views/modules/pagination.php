<div class="pagination">

	<a <?=$first->href?> <?=$first->title?> class="btn white first <?=$first->disabled?>">◄◄</a>
	<a <?=$previous->href?> <?=$previous->title?> class="btn white previous <?=$previous->disabled?>">◄</a>

	<?foreach ($pages as $page):?>
	<a href="<?=$page->url?>" class="btn white number <?=$page->active?>"><?=$page->count?></a>
	<?endforeach?>

	<a <?=$next->href?> <?=$next->title?> class="btn white next <?=$next->disabled?>">►</a>
	<a <?=$last->href?> <?=$last->title?> class="btn white last <?=$last->disabled?>">►►</a>

</div>