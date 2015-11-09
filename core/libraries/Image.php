<?php
if (!parse_class('Image')):
/**
 * Image Class
 *
 * Classe responsável pela manipulação de imagens.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.6
 * @version 			0.2
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

class Image extends Library {

	public $image;
	public $ext;
	public $width;
	public $height;

	/**
	 * Constrói o objeto da imagem.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @uses Library::__construct
	 *
	 * @param $image (string) - Caminho para a imagem.
	 */
	public function __construct($image) {
		parent::__construct();

		$this->ext = strtolower(ext($image));

		switch ($this->ext) {
			case 'gif':
			$create_image = 'imageCreateFromGif';
			break;

			case 'png':
			$create_image = 'imageCreateFromPng';
			break;

			default:
			case 'jpg':
			case 'jpeg':
			$create_image = 'imageCreateFromJpeg';
		}

		$this->image = $create_image($image);
	}

	/**
	 * Copia e, opcionalmente, redimensiona a imagem para outro local.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @param $new_image (string) - Caminho onde será salva a cópia da imagem.
	 * @param $new_width (integer) - Largura da cópia.
	 * @param $new_height (integer) - Altura da cópia.
	 *
	 * @return TRUE em caso de sucesso ou FALSE em falhas.
	 */
	public function copy($new_image, $new_width = null, $new_height = null) {
		if (!isset($this->width)) {
			$this->width = imagesx($this->image);
		}

		if (!isset($this->height)) {
			$this->height = imagesy($this->image);
		}

		$new_width = isset($new_width)? $new_width : $this->width;
		$new_height = isset($new_height)? $new_height : floor($this->height * ($new_width / $this->width));
		$copy = imagecreatetruecolor($new_width, $new_height);

		imagecopyresampled($copy, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);

		return imagejpeg($copy, $new_image);
	}

	/**
	 * Cria uma miniatura a partir da imagem do objeto.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @uses Image::copy
	 *
	 * @param $new_image (string) - Caminho onde será salva a miniatura da imagem.
	 * @param $new_width (integer) - Largura da miniatura.
	 *
	 * @return TRUE em caso de sucesso ou FALSE em falhas.
	 */
	public function thumbnail($new_image, $new_width = null) {
		if (!isset($this->width)) {
			$this->width = imagesx($this->image);
		}

		if (!isset($new_width)) {
			$new_width = $this->width / 4;
		}

		return $this->copy($new_image, $new_width);
	}

}
endif;
?>