<?php
include_once 'Begin.php';

/**
 * Página grade de programação
 */
class Schedule extends Begin {

	public $slug = 'grade';

	public $actions = array(
		'days' => '',
		'programs' => 'programas',
		'songs' => 'musicas',
		'getDay' => false,
		'getDayByTitle' => false,
		'getDayBySlug' => false,
		'getProgram' => false,
		'getProgramByTitle' => false,
		'getProgramBySlug' => false,
		'getProgramByDate' => false,
		'getCurrentProgram' => false,
		'getNextProgram' => false,
		'getSong' => false,
		'getSongs' => false,
		'getSongByTitle' => false,
		'getSongBySlug' => false,
		'getSchedule' => false,
		'getScheduleList' => false
	);

	public $currentProgramDate;
	private $scheduleList;

	/**
	 * Inicia a página
	 */
	public function init() {
		parent::init();

		$this->vars['sidebar_modules'][] = $this->load->module('iTwitter')->render(true, 3);
	}

	/**
	 * Index da grade de programação
	 */
	public function index() {
		$schedule_list = $this->getScheduleList(mktime(), strtotime('+24 hours'));

		if ($schedule_list) {
			// Tenta capturar um programa aleatório que possua músicas frequentes
			$count = 0;
			$limit = count($schedule_list); # Evita um looping infinito

			do {
				$count++;
				$schedule_program = $schedule_list[array_rand($schedule_list)];
				$program = $this->getProgram($schedule_program['id']);
			}
			while (!$program->songs && $count < $limit);

			// Define o título do browser
			if ($program->songs) {
				$browser_title = $this->page->vars(array(
					'song' => $this->getRandomSongByProgram($program)->label,
					'hour' => $schedule_program['hour']
				))->attributes()->browser_title;

				$this->set('browser_title', $browser_title);
			}
		}

		// Define os dias
		$days = array();

		foreach ($this->model->children() as $day => $model) {
			$days[] = $this->getDay($day);
		}

		$this->set('days', $days);
		$this->set('page', $this->render(true));

		$this->render('layout');
	}

	/**
	 * Action days
	 */
	public function days($day) {
		$day = $this->getDayBySlug($day);

		if ($day->programs) {
			// Tenta capturar um programa aleatório que possua músicas frequentes
			$count = 0;
			$limit = count($day->programs); # Evita um looping infinito

			do {
				$count++;
				$program = $day->programs[array_rand($day->programs)];
			}
			while (!$program->songs && $count < $limit);

			// Define o título do browser
			if ($program->songs) {
				$browser_title = $this->action->page->vars(array(
					'day' => $day->title,
					'song' => $this->getRandomSongByProgram($program)->label,
					'hour' => $program->hour
				))->attributes()->browser_title;

				$this->set('browser_title', $browser_title);
			}
		}

		// Define as meta tags
		$this->set('meta_description', $this->action->page->vars(array('day' => $day->title))->attributes()->meta_description);

		// Captura o dia anterior
		if ($prev = $this->model->xpath("$day->name/preceding-sibling::*")) {
			$prev = $this->getDay(end($prev)->getName());
		}
		else {
			$prev = $this->getDay($this->model->xpath('*[last()]', true)->getName());
		}

		// Captura o próximo dia
		if ($next = $this->model->xpath("$day->name/following-sibling::*", true)) {
			$next = $this->getDay($next->getName());
		}
		else {
			$next = $this->getDay($this->model->xpath('*[1]', true)->getName());
		}

		$this->set(compact('prev', 'next', 'day'));
		$this->set('page_subtitle', $this->action->page['title']->toString());
		$this->set('page', $this->render('schedule.day', true));

		$this->render('layout');
	}

	/**
	 * Realiza a paginação da action days
	 */
	public function _paginateDays($day = null) {
		$this->load->library('Inflector', 'inflect');
		$days = array();

		if (isset($day)) {
			$day = strtolower($day);

			if ($model = $this->model->xpath($day, true)) {
				$days[$day] = isset($model['slug'])? $model['slug']->toString() : $this->inflect->slug($model['title']);

				return $days;
			}
			else return false;
		}
		else {
			foreach ($this->model->children() as $day => $model) {
				$days[$day] = isset($model['slug'])? $model['slug']->toString() : $this->inflect->slug($model['title']);
			}

			return $days;
		}
	}

	/**
	 * Action programs
	 */
	public function programs($program_slug) {
		// Carrega algumas coisas
		$this->load->library('Inflector', 'inflect');
		$this->load->library('Html', 'html');
		$this->load->model('programs');

		$program = $this->getProgramBySlug($program_slug);

		// Define os títulos
		$this->action->page = $this->action->page->vars(array('program' => $program->title));
		$this->set('page_subtitle', $this->action->page['title']->toString());
		$this->set('browser_title', $this->action->page['browser_title']->toString());

		// Define as meta tags
		$this->set('meta_description', $this->action->page->vars(array('program_desc' => $program->desc))->attributes()->meta_description);
		$this->vars['meta_keywords'] .= isset($this->vars['meta_keywords'])? ', '.strtolower($program->genre) : strtolower($program->genre);

		$special_labels = array('hours');
		$labels = $this->programs->attributes()->toArray();
		$elements = array();

		// Para cada label, verifica se existe o elemento no programa e define para a view
		foreach ($labels as $attr => $label) {
			if (empty($program->$attr) && !in_array($attr, $special_labels)) continue;

			$element = new stdClass;
			$element->id = $this->inflect->camelize($attr);
			$element->label = $label->toString();

			switch ($attr) {
				case 'dj':
					$s = count($program->djs) > 1? 's' : '';
					$element->label = preg_replace('/\{s}/', $s, $element->label);
					$element->text = array();

					foreach ($program->djs as $date => $dj) {
						$element->text[] = is_numeric($date)? $dj : "$dj ($date)";
					}
					break;
				case 'hours':
					$schedules = $this->model->xpath(sprintf('//program[@id="%s"]', $program->id));
					$element->text = array();

					foreach ($schedules as $schedule) {
						$element->text[] = $this->getSchedule($schedule)->fullDate;
					}
					break;
				case 'songs':
					$element->text = array();

					foreach ($program->songs as $song) {
						$element->text[] = $this->html->tag('a', $song->label, array('href' => $song->url));
					}
					break;
				default:
					$element->text = $program->$attr;
			}

			$elements[] = $element;
		}

		$this->set(compact('program', 'elements'));
		$this->set('page', $this->render('schedule.program', true));

		$this->render('layout');
	}

	/**
	 * Realiza a paginação da action programs
	 */
	public function _paginatePrograms($id = null) {
		$this->load->library('Inflector', 'inflect');
		$this->load->model('programs');
		$programs = array();

		if (isset($id)) {
			if ($program = $this->programs->xpath("program[@id='$id']", true)) {
				$programs[$id] = isset($program['slug'])? $program['slug']->toString() : $this->inflect->slug($program['title']);

				return $programs;
			}
			else return false;
		}
		else {
			foreach ($this->programs->xpath('program') as $program) {
				$programs[$program['id']->toString()] = isset($program['slug'])? $program['slug']->toString() : $this->inflect->slug($program['title']);
			}

			return $programs;
		}
	}

	/**
	 * Action songs
	 */
	public function songs($song_slug) {
		// Carrega algumas coisas
		$this->load->library('Inflector', 'inflect');
		$this->load->library('Html', 'html');
		$this->load->model('songs');
		$this->load->model('programs');

		$song = $this->getSongBySlug($song_slug);

		// Define os títulos
		$this->action->page = $this->action->page->vars(array('song' => $song->label));
		$this->set('page_subtitle', $this->action->page['title']->toString());
		$this->set('browser_title', $this->action->page['browser_title']->toString());

		// Define as meta tags
		$this->set('meta_description', $this->action->page->vars(array('song' => $song->label))->attributes()->meta_description);

		$labels = $this->songs->attributes();
		$special_labels = array('programs');
		$elements = array();

		// Para cada label, verifica se existe o elemento na song e define para a view
		foreach ($labels as $attr => $label) {
			if (empty($song->$attr) && !in_array($attr, $special_labels)) continue;

			$element = new stdClass;
			$element->id = $this->inflect->camelize($attr);
			$element->label = $label->toString();

			if ($attr == 'programs') {
				// Captura os IDs dos programas que tocam tal música
				$programs_ids = $this->programs->xpath(sprintf('//song[@id="%s"]/../../@id', $song->id));
				$element->text = array();

				// Para cada ID, captura o programa e compôe a lista com seu título anexado ao link
				foreach ($programs_ids as $program_id) {
					$program = $this->getProgram($program_id);
					$element->text[] = $this->html->tag('a', $program->title, array('href' => $program->url));
				}
			}
			else {
				$element->text = $song->$attr;
			}

			$elements[] = $element;
		}

		$this->set(compact('song', 'elements'));
		$this->set('page', $this->render('schedule.song', true));

		$this->render('layout');
	}

	/**
	 * Realiza a paginação da action songs
	 */
	public function _paginateSongs($id = null) {
		$this->load->library('Inflector', 'inflect');
		$this->load->model('songs');
		$songs = array();

		if (isset($id)) {
			if ($song = $this->songs->xpath("song[@id='$id']", true)) {
				$songs[$id] = isset($song['slug'])? $song['slug']->toString() : $this->inflect->slug($song['artist'].' '.$song['title']);

				return $songs;
			}
			else return false;
		}
		else {
			foreach ($this->songs->xpath('song') as $song) {
				$songs[$song['id']->toString()] = isset($song['slug'])? $song['slug']->toString() : $this->inflect->slug($song['artist'].' '.$song['title']);
			}

			return $songs;
		}
	}

	/**
	 * Captura um dia pelo seu nome original em inglês
	 */
	public function getDay($day) {
		$this->orderSchedule();
		$day = (string) $day;

		if ($model = $this->model->xpath($day, true)) {
			$_day = new stdClass;
			$_day->name = $model->getName();
			$_day->url = $this->load->action('days')->url($day);
			$_day->programs = array();

			foreach ($model->attributes() as $attr => $value) {
				$_day->$attr = $value->toString();
			}

			foreach ($model->xpath('program') as $program) {
				$_day->programs[] = $this->getProgram($program['id'], $this->getSchedule($program));
			}

			return $_day;
		}
		else return false;
	}

	/**
	 * Captura um dia pelo seu título
	 */
	public function getDayByTitle($title) {
		if ($model = $this->model->xpath("*[@title='$title']", true)) {
			return $this->getDay($model->getName());
		}
		else return false;
	}

	/**
	 * Captura um dia pelo seu slug
	 */
	public function getDayBySlug($slug) {
		$days = $this->load->action('days')->paginate();
		$day = array_search($slug, $days);

		return $this->getDay($day);
	}

	/**
	 * Captura um programa pelo seu ID
	 */
	public function getProgram($id, $schedule = null) {
		$this->load->model('programs');
		$id = (string)$id;

		// Captura o programa no model
		if ($model = $this->programs->xpath("program[@id='$id']", true)) {
			$program = new stdClass;
			$program->url = $this->load->action('programs')->url($id);
			$program->djs[] = isset($model['dj'])? $model['dj']->toString() : 'AutoDJ'; # AutoDJ é o padrão
			$program->songs = array();

			foreach ($model->attributes() as $attr => $value) {
				$program->$attr = htmlspecialchars($value->toString());
			}

			foreach ($model->children() as $attr => $value) {
				if ($attr != 'songs') {
					$program->$attr = $value;
				}
			}

			// Captura as músicas do programa e trata sua frequência
			foreach ($model->xpath('songs/*') as $song) {
				if (isset($song['frequency'])) {
					eval("\$frequency = $song[frequency];");
				}

				$_song = $this->getSong($song['id']);

				if ($_song) {
					$_song->frequency = isset($frequency)? $frequency : 1; # Frequência padrão: 1
					$program->songs[] = $_song;
				}
				else {
					trigger_error("Song $song[id] was not found");
				}
			}

			// Se schedule foi definido nos parâmetros, define...
			if (isset($schedule)) {
				$program->day = $schedule->day;
				$program->hour = $schedule->hour;
				$program->fullHour = $schedule->fullHour;
				$program->fullDate = $schedule->fullDate;
				$program->dj = isset($schedule->dj)? $schedule->dj : null;
			}

			// Faz um looping por todos os schedules que possuem o mesmo programa, porém com outro DJ definido
			foreach ($this->schedule->xpath("//program[@id='$id'][@dj]") as $schedule) {
				$key = $this->getSchedule($schedule)->fullDate;
				$program->djs[$key] = $schedule['dj']->toString();
			}

			// Se depois de tudo o programa não tiver definido um DJ padrão, pega o primeiro dos DJs (AutoDJ)
			if (!isset($program->dj)) {
				$program->dj = reset($program->djs);
			}

			return $program;
		}
		else {
			trigger_error("Program $id was not found");
			return false;
		}
	}

	/**
	 * Captura um programa pelo seu título
	 */
	public function getProgramByTitle($title) {
		$this->load->model('programs');
		$id = $this->programs->xpath("program[@title='$title']/@id", true);

		return $this->getProgram($id);
	}

	/**
	 * Captura um programa pelo seu slug
	 */
	public function getProgramBySlug($slug) {
		$programs = $this->load->action('programs')->paginate();
		$id = array_search($slug, $programs);

		return $this->getProgram($id);
	}

	/**
	 * Captura um programa por um timestamp
	 */
	public function getProgramByDate($timestamp) {
		$this->orderSchedule();
		$ids = array();

		$timestamp = date('wHi', $timestamp);

		// Faz um looping pela lista de programação e armazena os programas cujo timestamp é menor que o timestamp atual
		foreach ($this->scheduleList as $program_ts => $program) {
			if ($program_ts <= $timestamp) {
				$ids[$program_ts] = $program['id'];
			}
		}
		reset($this->scheduleList);

		// Se depois de tudo $ids permanecer vazio, o horário atual está entre o último programa da semana e o primeiro, entre sábado e domingo
		// Portanto, reseta a lista e armazena o ID do primeiro programa
		if (!$ids) {
			$ids[key($this->scheduleList)] = current($this->scheduleList)->attributes()->id;
		}

		if ($ids) {
			// Ordena os IDs pelas chaves e pega o último ID armazenado
			ksort($ids);
			$id = end($ids);
			$key = key($ids);

			// Mantém o ponteiro da lista de programação no ponto do programa recuperado
			while (key($this->scheduleList) != $key) next($this->scheduleList);

			// Captura o programa
			$program = $this->getProgram($id, $this->getSchedule($this->scheduleList[$key]));

			return $program;
		}

		return false;
	}

	/**
	 * Captura o programa atual
	 */
	public function getCurrentProgram() {
		return $this->getProgramByDate(strtotime('now', strtotime($this->config['hour_diff'])));
	}

	/**
	 * Captura o próximo programa
	 */
	public function getNextProgram() {
		// A condição será falsa quando estivermos no último programa do sábado e o próximo for o primeiro, no domingo
		$model_program = next($this->scheduleList)? current($this->scheduleList) : reset($this->scheduleList);

		$program = $this->getProgram($model_program['id'], $this->getSchedule($model_program));

		return $program;
	}

	/**
	 * Captura uma música pelo seu ID
	 */
	public function getSong($id) {
		$this->load->model('songs');
		$id = (string)$id;

		// Procura no model pela song cujo ID bate com $id
		if ($model = $this->songs->xpath("//song[@id='$id']", true)) {
			$song = new stdClass;

			foreach ($model->attributes() as $attr => $value) {
				$song->$attr = htmlspecialchars($value->toString());
			}

			$song->url = $this->load->action('songs')->url($id);
			$song->label = isset($song->artist)? "$song->artist - $song->title" : $song->title;

			return $song;
		}

		return false;
	}

	/**
	 * Captura as músicas
	 */
	public function getSongs($limit = null) {
		$this->load->model('songs');

		$model_songs = $this->songs->xpath('song', $limit);
		$songs = array();

		foreach ($model_songs as $song) {
			$songs[] = $this->getSong($song['id']);
		}

		return $songs;
	}

	/**
	 * Captura uma música pelo seu título
	 */
	public function getSongByTitle($title) {
		$this->load->model('songs');
		$id = $this->songs->xpath("song[@title='$title']/@id", true);

		if (!$id) {
			// Se a música ainda não foi encontrada, verifica se consta o autor
			if (preg_match('/(^.+?) ?\- ?([^ ].+)$/', $title, $matches)) {
				list( , $artist, $title) = $matches;
				$id = $this->songs->xpath("song[@title='$title'][@artist='$artist']/@id", true);
			}
		}

		return $this->getSong($id);
	}

	/**
	 * Captura uma música pelo seu slug
	 */
	public function getSongBySlug($slug) {
		$songs = $this->load->action('songs')->paginate();
		$id = array_search($slug, $songs);

		return $this->getSong($id);
	}

	/**
	 * Captura uma música aleatória de um programa
	 */
	public function getRandomSongByProgram($program, $include_dj = false) {
		$songs = array();

		// Prepara as músicas para serem sorteadas
		foreach ($program->songs as $song) {
			$frequency = ceil($song->frequency * 10);

			for ($i = 0; $i < $frequency; $i++) $songs[] = $song;
		}

		// Caso seja para incluir o DJ, apenas insere um valor falso nas músicas, de modo que, caso este seja sorteado,
		// não há música para exibir
		if ($include_dj && strtolower($program->dj) != 'autodj') {
			$songs[] = false;
		}

		// Sorteia a música
		if ($songs) {
			return $songs[array_rand($songs)];
		}
		else return false;
	}
	/**
	 * Captura um schedule pelo seu model
	 */
	public function getSchedule($model) {
		$this->load->library('Html', 'html');

		$this->orderSchedule();
		$timestamp = $model['timestamp']->toString();
		$schedule_list = $this->scheduleList;

		// Se o timestamp do schedule não existir, não há o que fazer
		if (!isset($schedule_list[$timestamp])) return false;

		// Realiza o processo para que o ponteiro da lista de programação se mantenha na posição do schedule
		if ($timestamp != current($schedule_list)->attributes()->timestamp) {
			reset($schedule_list);

			while ($timestamp != current($schedule_list)->attributes()->timestamp) {
				next($schedule_list);
			}
		}

		// Captura o próximo schedule
		$next_schedule = next($schedule_list)? current($schedule_list) : reset($schedule_list);

		// Inicia o schedule
		$schedule = new stdClass;

		foreach ($model->attributes() as $attr => $value) {
			$schedule->$attr = $value->toString();
		}

		$day = $model->xpath('parent::*', true);
		$schedule->day = $day['title']->toString();

		$full_hour = array('{begin}' => $schedule->hour, '{end}' => $next_schedule['hour']->toString());
		$schedule->fullHour = str_replace(array_keys($full_hour), array_values($full_hour), $this->model['full_hour']);

		$dayurl = $this->load->action('days')->url($day->getName());
		$full_date = array('{day}' => $this->html->tag('a', $schedule->day, array('href' => $dayurl)), '{full_hour}' => $schedule->fullHour);
		$schedule->fullDate = str_replace(array_keys($full_date), array_values($full_date), $this->model['full_date']);

		return $schedule;
	}

	/**
	 * Captura uma lista de programação baseada em um tempo de início e fim
	 */
	public function getScheduleList($begin = null, $limit = null) {
		$this_schedule_list = $this->orderSchedule();

		if (!isset($begin)) $begin = strtotime('now');
		if (!isset($limit)) $limit = strtotime('+1 week', $begin);

		$begin_week = date('w', $begin);
		$begin_ts = $begin_week.date('Hi', $begin);
		$limit_ts = date('wHi', $limit);
		$schedule_list = array();

		foreach ($this_schedule_list as $program_ts => $program) {
			// A segunda condição será verdadeira somente até o final de sábado (6)
			if (($program_ts > $begin_ts && $program_ts < $limit_ts)
			OR ($limit_ts <= $begin_ts && $program_ts > $begin_ts)) {
				$schedule_list[$program_ts] = $program;
				unset($this_schedule_list[$program_ts]);
			}
		}

		// Este looping complementa a segunda condição do looping anterior
		// Ele captura os que estão antes do limit, ou seja, no início da semana
		// A necessidade de um looping à parte se dá porque essas programações do início da semana, neste caso,
		// precisam estar no final do array retornado
		if ($limit_ts <= $begin_ts) {
			foreach ($this_schedule_list as $program_ts => $program) {
				if ($program_ts < $limit_ts) {
					$schedule_list[$program_ts] = $program;
				}
			}
		}

		return $schedule_list;
	}

	/**
	 * Ordena adequadamente a grade de programação
	 */
	private function orderSchedule() {
		if (!isset($this->scheduleList)) {
			$schedule_list = array();

			foreach ($this->model->children() as $day => $content) {
				$weekday = date('w', strtotime("next $day"));

				foreach ($content->xpath('program') as $program) {
					$timestamp = $program['timestamp'] = $weekday.preg_replace('/\D/', '', $program['hour']);
					$schedule_list[$timestamp] = $program;
				}
			}

			ksort($schedule_list);
			$this->scheduleList = $schedule_list;
		}

		return $this->scheduleList;
	}
}
?>