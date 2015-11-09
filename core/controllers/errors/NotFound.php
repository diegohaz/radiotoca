<?php
if (!class_exists('NotFound')):
/**
 * NotFound Error
 *
 * Página de erro que será mostrada quando a página requisitada não for encontrada.
 *
 * @package				core.controllers.errors
 * @since 				Neleus 0.2
 * @version 			0.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

class NotFound extends ErrorController {

	/**
	 * Inicia o controller.
	 *
	 * @since 				Neleus 0.2
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	17/07/2010
	 */
	public function init() {
		$vars['uri'] = isset($this->params['uri'])? $this->params['uri'] : urldecode($_SERVER['REQUEST_URI']);
		$this->model = $this->model->vars($vars);

		$content['title'] = $this->model->title;
		$content['header'] = $this->model->header;
		$content['descs'] = $this->model->xpath('desc');

		$this->set($content);
		$this->render();
	}

}
endif;
?>