<?php
/**
 * @package				core.views.pages
 * @since 				Neleus 0.2
 * @version 			0.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$title?></title>

</head>
<body>

	<h1><?=$header?></h1>

	<?foreach($descs as $desc):?>
	<p><?=$desc?></p>
	<?endforeach?>

</body>
</html>