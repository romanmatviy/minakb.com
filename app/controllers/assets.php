<?php

class assets extends Controller {

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
                case 'js':
                    header("Content-Type: application/javascript");
                    break;
            }

            readfile($path);
            exit();
        }
    	$this->load->page_404();
    }

}

?>