<?php
if (!class_exists('NoView')):
/**
 * NoView Error
 *
 * Erro que será exibido quando a view não for encontrada.
 *
 * @package				core.controllers.errors
 * @since 				Neleus 0.2
 * @version 			0.5
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

class NoView extends ErrorController {

	/**
	 * Inicia o controller.
	 *
	 * @since 				Neleus 0.2
	 * @version 			0.9
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/07/2010
	 */
	public function init() {
		if (isset($this->params['controller'])) {
			$controller = $this->params['controller'];

			$vars['file'] = $controller->currentViewSource? basename($controller->currentViewSource) : basename($controller->view->source);
			$vars['dir'] = $controller->currentViewSource? nice_dir(dirname($controller->currentViewSource)) : nice_dir(dirname($controller->view->source));
			$vars['view'] = filename($vars['file']);

			$this->model = $this->model->vars($vars);

			$content['title'] = $this->model->title;
			$content['header'] = $this->model->header;
			$content['descs'] = $this->model->xpath('desc');

			$this->set($content);
			$this->render();
		}
		else {
			echo 'View not found';
		}
	}

}
endif;
?>