<?php 

class wl_sections_admin extends Controller {

	public function add()
	{
		$where = $this->data->prepare(['alias_id' => 'number', 'content_id' => 'number']);
		$data = $this->data->prepare(['alias_id' => 'number', 'content_id' => 'number', 'type', 'name', 'access', 'position' => 'number', 'title']);
		if(!in_array($data['type'], ['text_single', 'text_multi', 'textarea_single', 'textarea_multi', 'images', 'videos', 'audios', 'files']))
			$data['type'] = 'text_single';
		if(!in_array($data['access'], ['all', 'login', 'manager']))
			$data['access'] = 'all';
		if(empty($data['alias_id']))
			exit('Error: alias_id empty!');
		$data['name'] = $this->data->latterUAtoEN($data['name']);
		$data['value'] = $data['attr'] = '';

		if($data['position'] == 0)
			$data['position'] = $this->db->getCount('wl_sections', $where) + 1;

		if($data['section_id'] = $this->db->insertRow('wl_sections', $data))
		{
			if ($_SESSION['language'] && in_array($data['type'], ['images', 'text_multi', 'textarea_multi']))
			{
				$data['language'] = $_SESSION['language'];
				foreach ($_SESSION['all_languages'] as $lang) {
					$data['value_' . $lang] = '';
				}
			}
			$this->__after_update($data['alias_id'], $data['content_id']);

			$res = ['update' => false, 'js_init' => false, 'name' => $data['name']];
			if (empty($data['name']))
				$res['name'] = 'section_id #' . $data['section_id'];

			$data['echoSectionTag'] = true;
			ob_start();
			$this->load->view("admin/wl_sections/__section_{$data['type']}", $data);
			$res['html'] = ob_get_contents();
			if (!empty($_SESSION['alias']->js_init))
				$res['js_init'] = implode('; ', $_SESSION['alias']->js_init);
			ob_end_clean();
			$this->load->json($res);
		}
	}

	public function get()
	{
		if($section_id = $this->data->post('section_id'))
			if(is_numeric($section_id) && $section_id > 0)
				if($section = $this->db->getAllDataById('wl_sections', $section_id))
					$this->load->json($section);
	}

	public function set()
	{
		$fields = ['type', 'name', 'attr', 'access', 'title', 'value'];
		if($_SESSION['language'])
			foreach ($_SESSION['all_languages'] as $lang) {
				$fields[] = 'title-'.$lang;
				$fields[] = 'value-'.$lang;
			}
		if($section_id = $this->data->post('section_id'))
			if($field = $this->data->post('field'))
				if(is_numeric($section_id) && $section_id > 0)
					if(in_array($field, $fields))
						if($section = $this->db->getAllDataById('wl_sections', $section_id))
						{
							$value = $this->data->post('value');
							if($field == 'name')
								$value = $this->data->latterUAtoEN($value);
							if($field == 'type')
							{
								if(!in_array($value, ['text_single', 'text_multi', 'textarea_single', 'textarea_multi', 'images', 'videos', 'audios', 'files']))
									$value = 'text_single';
							}
							if($field == 'access')
							{
								if(!in_array($value, ['all', 'login', 'manager']))
									$value = 'all';
							}
							if ($_SESSION['language'] && in_array($section->type, ['text_multi', 'textarea_multi', 'images']))
							{
								$section->language = $_SESSION['language'];
								$t = @unserialize($section->title);
								$v = @unserialize($section->value);

								$fields = [];
								foreach ($_SESSION['all_languages'] as $lang) {
									$fields[] = 'title-' . $lang;
									$fields[] = 'value-' . $lang;
									$section->{'title_' . $lang} = $t[$lang] ?? '';
									$section->{'value_' . $lang} = $v[$lang] ?? '';
								}
								if(in_array($field, $fields))
								{
									$f = explode('-', $field);
									$field = $f[0];
									$lang = $section->language = $f[1];
									
									if($field == 'title')
									{
										$t[$lang] = $value;
										$value = serialize($t);
									}
									else
									{
										$v[$lang] = $value;
										$value = serialize($v);
									}
								}
							}

							if($section->$field != $value)
							{
								$section->$field = $value;
								$this->db->updateRow('wl_sections', [$field => $value], $section_id);

								$this->__after_update($section->alias_id, $section->content_id);
								
								$res = ['update' => false, 'js_init' => false, 'name' => $section->title];
								if(!empty($section->language) && !empty($section->title))
								{
									$t = unserialize($section->title);
									$res['name'] = $t[ $section->language ] . ' / '. $section->language;
								}
								if(empty($section->title))
								{
									$res['name'] = $section->name;
									if(empty($section->name))
										$res['name'] = 'section_id #'.$section->id;
								}

								if($field == 'type')
								{
									$res['update'] = true;

									$data = (array) $section;
									$data['section_id'] = $data['id'];
									$data['echoSectionTag'] = false;
									ob_start();
									$this->load->view("admin/wl_sections/__section_{$data['type']}", $data);
									$res['html'] = ob_get_contents();
									if(!empty($_SESSION['alias']->js_init))
										$res['js_init'] = implode('; ', $_SESSION['alias']->js_init);
							        ob_end_clean();
								}
								$this->load->json($res);
							}
						}
	}

	function delete()
	{
		if($section_id = $this->data->post('section_id'))
			if(is_numeric($section_id) && $section_id > 0)
				if($section = $this->db->getAllDataById('wl_sections', $section_id))
				{
					if($section->type == 'images')
					{
						if($wl_images = $this->db->getAllDataByFieldInArray('wl_images', $section_id, 'section_id'))
						{
							$ids = [];
							foreach($wl_images as $i) {
								$ids[] = $i->id;
							}

							$alias = $this->db->getAllDataById('wl_aliases', $section->alias_id);
							$path = IMG_PATH.$alias->alias.'/'.$section->content_id.'/';
		                    $path = substr($path, strlen(SITE_URL));
		                    $prefix = array('');
		                    if($sizes = $this->db->getAliasImageSizes($section->alias_id))
		                        foreach ($sizes as $resize) {
		                            $prefix[] = $resize->prefix.'_';
		                        }
		                    foreach ($prefix as $p) {
		                    	foreach($wl_images as $i) {
									$filename = $path.$p.$i->file_name;
		                        	@unlink ($filename);
								}
		                    }

		                    $this->db->deleteRow('wl_images', $section_id, 'section_id');
		                    if($_SESSION['language'])
		                        $this->db->deleteRow('wl_media_text', array('type' => 'photo', 'content' => $ids));
						}
					}

					$this->db->deleteRow('wl_sections', $section_id);
					$this->__after_update($section->alias_id, $section->content_id);
					exit ("true");
				}
		echo 'false';
	}

	private function __after_update($alias_id, $content_id)
	{
		$ntkd = $this->db->getAllDataByFieldInArray('wl_ntkd', ['alias' => $alias_id, 'content' => $content_id]);
            foreach ($ntkd as $row) {
                if(empty($row->get_sivafc) || in_array('s', str_split($row->get_sivafc)) === false)
                {
                    if(empty($row->get_sivafc))
                        $this->db->updateRow('wl_ntkd', ['get_sivafc' => 's'], $row->id);
                    else
                        $this->db->updateRow('wl_ntkd', ['get_sivafc' => $row->get_sivafc.'s'], $row->id);
                }
            }
        $this->db->html_cache_clear($content_id, $alias_id);

        $this->load->function_in_alias($alias_id, '__after_edit', $content_id, true);
	}

}

 ?>