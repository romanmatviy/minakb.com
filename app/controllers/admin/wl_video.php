<?php

class wl_video_admin extends Controller {
				
    function _remap($method)
    {
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            $this->index($method);
        }
    }

    function index()
    {
    	header("Location: ".SITE_URL);
    	exit();
	}
	
	function save($return = false)
	{
		if(isset($_POST['alias']) && is_numeric($_POST['alias']) && isset($_POST['content']) && is_numeric($_POST['content']) && !empty($_POST['video']))
		{
			$videolink = $this->data->post('video', true); 
			$controler_video=parse_url($videolink);
			$site = '';
			if(!empty($controler_video['host'])){			
				if (($controler_video['host']=="youtu.be") || ($controler_video['host']=="www.youtube.com") || ($controler_video['host']=="youtube.com")) {
				$site="youtube";
					if ($controler_video['host']=="youtu.be"){
						$site_link=substr($controler_video['path'],1);
					} elseif (array_key_exists('query', $controler_video)){
						$first_marker = strpos( $controler_video['query'], '=')+1;
						$second_marker=strpos( $controler_video['query'], '&');
						if($second_marker != '') {$second_marker -=2;
							$site_link=substr($controler_video['query'],$first_marker,$second_marker);
						} else $site_link=substr($controler_video['query'],$first_marker);					
					} else {
						$site_link=str_replace('/embed/', '', $controler_video['path']);					
					}
				}
				elseif ($controler_video['host']=="vimeo.com"){
					$site="vimeo";
					$site_link=substr($controler_video['path'],1);
				}
			}
			if($site != '')
			{
				$data['author'] = $_SESSION['user']->id;
				$data['date_add'] = time();
				$data['alias'] = $_POST['alias'];
				$data['content'] = $_POST['content'];
				$data['section_id'] = $_POST['section_id'] ?? 0;
				$data['site'] = $site;
				$data['link'] = $site_link;
				$data['active'] = 1;

				if($this->db->insertRow('wl_video', $data))
				{
					$ntkd = $this->db->getAllDataByFieldInArray('wl_ntkd', ['alias' => $_POST['alias'], 'content' => $_POST['content']]);
						foreach ($ntkd as $row) {
							if(empty($row->get_sivafc) || in_array('v', str_split($row->get_sivafc)) === false)
							{
								if(empty($row->get_sivafc))
									$this->db->updateRow('wl_ntkd', ['get_sivafc' => 'v'], $row->id);
								else
									$this->db->updateRow('wl_ntkd', ['get_sivafc' => $row->get_sivafc.'v'], $row->id);
							}
						}

					if($return)
						return true;

					$this->db->html_cache_clear($_POST['content'], $_POST['alias']);
					$this->load->function_in_alias($_POST['alias'], '__after_edit', $_POST['content'], true);

					if($data['section_id'] > 0)
						$this->redirect('#tab-video');
					$this->redirect();
				}
			}
			else
			{
				$_SESSION['notify'] = new stdClass();
				$_SESSION['notify']->errors = 'Невірна адреса відео. Підтримуються сервіси youtu.be, youtube.com, vimeo.com!';

				if($return)
					return false;

				$this->redirect('#tab-video');
			}
		}
		else
			$this->load->page_404(false);
	}

	public function delete()
	{
		if(isset($_GET['id']) && is_numeric($_GET['id']))
		{
			if($video = $this->db->getAllDataById('wl_video', $_GET['id']))
			{
				$this->db->deleteRow('wl_video', $_GET['id']);

				$this->db->html_cache_clear($video->content, $video->alias);
				$this->load->function_in_alias($video->alias, '__after_edit', $video->content, true);
			}
			$this->redirect('#tab-video');
		}
		else
			$this->load->page_404(false);
	}
}
?>