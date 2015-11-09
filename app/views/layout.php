<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?=$this->config['encoding']?>" />
<?if (isset($meta_description)):?>
<meta name="description" content="<?=$meta_description?>" />
<?endif?>
<?if (isset($meta_keywords)):?>
<meta name="keywords" content="<?=$meta_keywords?>" />
<?endif?>

<title><?=$browser_title?></title>

<link rel="shortcut icon" href="<?=url()?>/favicon.ico" />
<link rel="canonical" href="<?=$this->controller->action->url($this->controller->action->params)?>" />
<link rel="index" href="<?=url()?>" />

<?if (!is_ie6()):?>
<link rel="stylesheet" type="text/css" media="all" href="<?=url()?>/css/style.css" />
<?else:?>
<script src="http://imasters.uol.com.br/crossbrowser/fonte.js" type="text/javascript"></script>
<?endif?>

<!--[if lte IE 8]>
<style type="text/css">
#tweets .tweet {
	border:1px solid #222;
}
</style>
<![endif]-->

</head>

<body id="<?=$this->controller->id?>" class="nojs">

<div id="main">

	<?if ($this->controller->id == 'Begin'):?>
	<div id="logoHome">
		<h1><?=$title?></h1>
	</div>
	<?endif?>

	<div class="wrapper">

		<div id="logo">
			<h1><a href="<?=url()?>" title="<?=$title?>"><?=$title?></a></h1>
		</div>

		<div id="sidebar">

			<?if (isset($actions)):?>
			<div id="actions">
				<p class="label actions"><?=$labels->actions?></p>
				<ul>
					<?foreach ($actions as $action):?>
					<li>
						<a href="<?=$action->url?>" class="<?=$action->id?> <?=$action->current?>" title="<?=$action->title?>">
							<span class="arrow"></span>
							<span class="title"><?=$action->currentTitle?></span>
							<span class="desc"><?=$action->desc?></span>
						</a>
					</li>
					<?endforeach?>
				</ul>
			</div>
			<?endif?>

			<?if (isset($sidebar_modules)):?>
			<?foreach ($sidebar_modules as $module) echo $module?>
			<?endif?>

		</div>

		<?if (isset($ischedule)) echo $ischedule?>
		<?if (isset($itwitter)) echo $itwitter?>

		<?if (isset($page)):?>
		<div id="page">
			<h2 class="label"><?=$page_title?></h2>
			<div id="content"><?=$page?></div>
		</div>
		<?endif?>

		<div class="clear"></div>

	</div>

	<div id="footer">
		<div class="wrapper"><div class="wrapper">

			<div id="ads"></div>

			<?if (isset($tops)):?>
			<div id="tops" class="section">
				<?if (isset($tops->listeners)) echo $tops->listeners?>
				<?if (isset($tops->songs)) echo $tops->songs?>
			</div>
			<?endif?>

			<div id="theLastBar">
				<div id="footerNav">
					<ul>
						<?foreach ($pages as $page):?>
						<li><a href="<?=$page->url?>"><?=$page->title?></a></li>
						<?endforeach?>
					</ul>
				</div>
				<div id="copyright"><p><?=$copyright->desc?></p></div>
				<div id="credits">
					<a href="<?=$credits->website?>" title="<?=$credits->desc?>" target="_blank"><?=$credits->desc?></a>
				</div>
			</div>

		</div></div>
	</div>

</div>

<?if (isset($sponsorship)):?>
<div id="sponsorship">
	<a href="<?=$sponsorship->website?>" title="<?=$sponsorship->name?>" target="_blank"><?=$sponsorship->desc?></a>
</div>
<?endif?>

<?if (isset($player)) echo $player?>

<div id="adsLoader">
	<script type="text/javascript"><!--
	google_ad_client = "ca-pub-0919539857027090";
	/* Rádio Toca - Banner principal */
	google_ad_slot = "3040433894";
	google_ad_width = 728;
	google_ad_height = 90;
	//-->
	</script>
	<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
</div>

<script type="text/javascript">
var index = '<?=url()?>';

var ads = document.getElementById('adsLoader');
document.getElementById('ads').appendChild(ads);
</script>

<script type="text/javascript" src="<?=url()?>/js/jquery.js"></script>
<script type="text/javascript" src="<?=url()?>/js/script.js"></script>

</body>
</html>
