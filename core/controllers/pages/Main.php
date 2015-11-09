<?php
if (!parse_class($this->config['main_page'])):
/**
 * Main Page
 *
 * Página padrão, será mostrada apenas se não existir uma Main na aplicação.
 *
 * @package				core.controllers.pages
 * @since 				Neleus 0.2
 * @version 			0.3.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	19/09/2010
 */

class Main extends PageController {

	public $view = 'core.main';
	public $model = 'core.main';

	/**
	 * Index da página principal.
	 *
	 * @since 				Neleus 0.2
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/07/2010
	 */
	public function index() {
		$vars['main_page'] = $this->config['main_page'];
		$vars['pages_dir'] = nice_dir($this->config['path'].CONTROLLERS.$this->config->dir['controllers']['pages']);
		$vars['config_dir'] = nice_dir($this->config['path'].CONFIG);

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