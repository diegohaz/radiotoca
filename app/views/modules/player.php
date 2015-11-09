<div id="player">
	<div id="swf"></div>
	<div class="info">
		<span></span>
		<div>
			<?if ($online):?>
			<p><strong>Música</strong> <?=$song?> <strong>Programa</strong> <?=$program->title?> <strong>DJ</strong> <?=$program->dj?></p>
			<?else:?>
			<p><?=$offline_message?></p>
			<?endif?>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?=url()?>/js/swfobject.js"></script>
<script type="text/javascript">
var s1 = new SWFObject("<?=url()?>/player.swf","ply","30","103","9");
s1.addParam("allowscriptaccess","always");
s1.write("swf");
</script>