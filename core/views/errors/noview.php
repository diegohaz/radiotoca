<?php
/**
 * @package				core.views.errors
 * @since 				Neleus 0.2
 * @version 			0.4
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */
?>
<div id="noView">

	<h1><?=$header?></h1>

	<?foreach($descs as $desc):?>
	<p><?=$desc?></p>
	<?endforeach?>

</div>