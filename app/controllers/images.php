<?php

class images extends Controller {

    function _remap($method, $data = array())
    {
        if (method_exists($this, $method)) {
            if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
            $this->index($method);
        }
    }

    public function index()
    {
        $path = $this->data->url(true);
        if(file_exists($path))
        {
            $ext = explode('.', $path);
            switch (end($ext)) {
                case 'jpg':
                case 'jpeg':
                    header("Content-type: image/jpg");
                    break;
                case 'png':
                    header("Content-type: image/png");
                    break;
                case 'gif':
                    header("Content-type: image/gif");
                    break;
                case 'svg':
                    header("Content-type: image/svg+xml");
                    break;
            }

            readfile($path);
            exit();
        }

    	if(count($this->data->url()) == 4)
    	{
            $folder = false;
            $alias = $this->data->uri(1);
            if($cache = $this->db->cache_get($alias, 'wl_aliases'))
            {
                if(isset($cache->alias) && !empty($cache->options))
                {
                    if(!empty($cache->options['folder']))
                    {
                        $folder = new stdClass();
                        $folder->alias = $cache->alias->id;
                        $folder->value = $cache->options['folder'];
                    }
                }
            }
            else
            {
                $folder = $this->db->getAllDataById('wl_options', array('value' => $alias, 'name' => 'folder', 'alias' => '>0'));
            }
            if(is_object($folder))
    		{
    			if(is_numeric($this->data->uri(2)))
    			{
    				$name = explode('_', $this->data->uri(3));
    				if(count($name) >= 2)
    				{
    					if($sizes = $this->db->getAliasImageSizes($folder->alias))
    					{
    						foreach ($sizes as $resize) {
                                $prefix = $name[0];
                                $count = count(explode('_', $resize->prefix));
                                if($count > 1)
                                {
                                    for ($i=1; $i < $count; $i++) { 
                                        if(isset($name[$i]))
                                            $prefix .= '_'.$name[$i];
                                    }
                                }
    							if($resize->prefix != '' && $resize->prefix == $prefix)
    							{
    								$name = substr($this->data->uri(3), strlen($resize->prefix) + 1);
    								$path = IMG_PATH.$folder->value.'/'.$this->data->uri(2).'/'.$name;
    								$path = substr($path, strlen(SITE_URL));
    								$this->load->library('image');
    								if($this->image->loadImage($path))
    								{
    									if(in_array($resize->type, array(1, 11, 12)))
				                            $this->image->resize($resize->width, $resize->height, $resize->quality, $resize->type);
				                        if(in_array($resize->type, array(2, 21, 22)))
				                            $this->image->preview($resize->width, $resize->height, $resize->quality, $resize->type);
				                        $this->image->save($resize->prefix);

				                        header("Content-type: image/".$this->image->getExtension());
				                        $path = IMG_PATH.$folder->value.'/'.$this->data->uri(2).'/'.$this->data->uri(3);
    									$path = substr($path, strlen(SITE_URL));
				                        readfile($path);
				                        exit();
    								}
    							}
    						}
    					}
    				}
    			}
    		}
    	}
    	$this->load->page_404(false);
    }

}

?>