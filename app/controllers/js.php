<?php

class js extends Controller {

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
            header("Content-Type: application/javascript");
            readfile($path);
            exit();
        }

    	if(count($this->data->url()) > 2)
    	{
            $alias = $this->data->uri(1);
            if($cache = $this->db->cache_get($alias, 'wl_aliases'))
            {
                if(isset($cache->alias))
                    $alias = $cache->alias;
            }
            else
            {
                $alias = $this->db->select('wl_aliases as a', 'service', $this->data->uri(1), 'alias')
                    ->join('wl_services', 'name as service_name', '#a.service')
                    ->get('single');
            }
            if(is_object($alias))
                if($alias->service > 0)
        		{
                    $path = APP_PATH.'services'.DIRSEP.$alias->service_name.DIRSEP.'js'.DIRSEP;
                    $url = $this->data->url();
                    array_shift($url);
                    array_shift($url);
                    $path .= implode(DIRSEP, $url);
                    if(file_exists($path))
                    {
                        header("Content-Type: application/javascript");
                        readfile($path);
                        exit();
                    }
        		}
    	}
    	$this->load->page_404(false);
    }

}

?>