<?php

class files extends Controller {

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
                case 'pdf':
                    header("Content-type: application/pdf");
                    break;
                case 'doc':
                    header("Content-type: application/doc");
                    break;
                case 'docx':
                    header("Content-type: application/docx");
                    break;
                case 'xls':
                    header("Content-type: application/xls");
                    break;
                case 'xlsx':
                    header("Content-Type: application/xlsx");
                    break;
                case 'pptx':
                    header("Content-Type: application/pptx");
                    break;
                case 'mp4':
                    header("Content-Type: application/mp4");
                    break;
            }

            readfile($path);
            exit();
        }
    	$this->load->page_404();
    }

}

?>