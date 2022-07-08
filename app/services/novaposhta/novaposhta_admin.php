<?php

/*

 	Service "NovaPoshta"
	for WhiteLion 1.0

*/

class novaposhta_admin extends Controller {
				
    function _remap($method, $data = array())
    {
    	// $_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index($uri)
    {
        $_SESSION['notify'] = new stdClass();
        $_SESSION['notify']->warning = 'Адмін інтерфейс для Нової пошти відсутній';
        $this->redirect('admin');
    }
	
}

?>