<?php

class style extends Controller {

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
                case 'css':
                    header("Content-Type: text/css");
                    break;
            }

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
                    $path = APP_PATH.'services'.DIRSEP.$alias->service_name.DIRSEP.'style'.DIRSEP;
                    $url = $this->data->url();
                    array_shift($url);
                    array_shift($url);
                    $path .= implode(DIRSEP, $url);
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
                            case 'css':
                                header("Content-Type: text/css");
                                break;
                            default:
                                $this->load->page_404();
                                break;
                        }

                        readfile($path);
                        exit();
                    }
        		}
    	}
    	$this->load->page_404(false);
    }

}

?>