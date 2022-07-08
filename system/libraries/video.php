<?php 

/*
	video 1.1 Бібліотека для виводу відеоплеєрів.
	1.0  27.11.2015 - Створено. Підтримується youtube.com, vimeo.com
	1.1  07.12.2015 - Додано задання розмірів по замовчуванню із конфігураційного файлу.
	1.2  10.03.2016 - Додано setVideosToText - заміну відео формату {video-###} у $_SESSION['alias']->text, makeVideosInText()
	1.2.1  26.07.2016 - адаптовано до php7
	1.2.2  20.13.2019 - fix size()
 */

class video
{
	public $width = 800;
	public $height = 450;
	private $mode = 'echo';

	 /*
     * Отримуємо дані для розміру по замовчуванню з конфігураційного файлу
     */
    function __construct($cfg = array())
    {
        if(!empty($cfg)){
        	$width = (isset($cfg['width']) && $cfg['width'] > 0) ? $cfg['width'] : 0;
        	$height = ($width > 0 && isset($cfg['height']) && $cfg['height'] > 0) ? $cfg['height'] : 0;
        	$this->size($width, $height);
        }
    }

	public function size($width, $height = 0)
	{
		$this->width = $width;
		if($height > 0)
			$this->height = $height;
		else if(is_numeric($width) && $width > 0)
			$this->height = round($width * 9 / 16);
		else
			$this->height = '';
	}

	public function show_many($videos, $after = '<br>', $start = '')
	{
		$this->mode == 'echo';
		if(is_array($videos) && !empty($videos)) { 
			foreach($videos as $video){
				$this->show($video, $after, $start);
			}
		} elseif(is_object($videos)) {
			$this->show($videos, $after, $start);
		}
	}

	public function show($video, $after = '', $start = '')
	{
		$text = '';
		switch ($video->site) {
			case 'youtube':
				$text = "<iframe width=\"{$this->width}\" height=\"{$this->height}\" src=\"https://www.youtube.com/embed/{$video->link}\" frameborder=\"0\" allowfullscreen></iframe>";
				break;
			case 'vimeo':
				$text = "<iframe src=\"https://player.vimeo.com/video/{$video->link}?color=ff0179\" width=\"{$this->width}\" height=\"{$this->height}\" frameborder=\"0\" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>";
				break;
		}
		if($this->mode == 'replace' && isset($video->replace_text) && $video->replace_text != '')
		{
			$_SESSION['alias']->text = mb_ereg_replace($video->replace_text, $text, $_SESSION['alias']->text);
		}
		else
		{
			echo($start.$text.$after);
		}
	}

	public function setVideosToText($videos)
	{
		$this->mode = 'replace';
		if(is_array($videos) && !empty($videos)) { 
			foreach($videos as $video){
				$this->show($video);
			}
		} elseif(is_object($videos)) {
			$this->show($videos);
		}
	}

	public function makeVideosInText()
	{
		$video = false;
		if(preg_match_all("#\{video-[0-9]+\}#is", $_SESSION['alias']->text, $video) > 0)
		{
			$videos = array();
			$videos_id = array();
			foreach ($video[0] as $v) {
				$id = substr($v, 7);
				$id = substr($id, 0, -1);
				$videos_id[$id] = $v;
			}
			foreach ($videos_id as $id => $text) {
				$video = $this->db->getAllDataById('wl_video', $id);
				if($video) {
					$video->replace_text = $text;
					$videos[] = $video;
				}
			}
			$this->setVideosToText($videos);
		}
		return false;
	}

}

?>