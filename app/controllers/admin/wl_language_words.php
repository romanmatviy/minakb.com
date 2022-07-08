<?php

class wl_language_words_admin extends Controller {
				
    function _remap($method)
    {
        $_SESSION['alias']->name = 'Список всіх слів та фраз сайту';
        $_SESSION['alias']->breadcrumb = array('Мультимовність' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
            $this->$method();
        else
            $this->index($method);
    }

    public function index()
    {
        if($_SESSION['user']->admin == 1)
        {
        	$this->load->model('wl_language_model');
        	$words = $this->wl_language_model->getAllWords();
        	$aliases = $this->db->getAllData('wl_aliases');
        	$alias = new stdClass();
        	$alias->id = 0;
        	$alias->alias = 'Загальні фрази та слова сайту (у шапці, підвалі..)';
        	array_unshift($aliases, $alias);
        	$this->load->admin_view('wl_language_words/index_view', array('words' => $words, 'aliases' => $aliases));
        }
    }

    public function save()
    {
    	if($_SESSION['user']->admin == 1 && $this->data->post('word') > 0)
        {
    		$this->load->model('wl_language_model');
    		$this->wl_language_model->save($this->data->post('word'), $this->data->post('language'), $this->data->post('value'));
    	}
    }

    public function delete()
    {
        if($_SESSION['user']->admin == 1 && $this->data->post('id') > 0)
        {
            $this->db->deleteRow('wl_language_words', $this->data->post('id'));
            $this->db->deleteRow('wl_language_values', $this->data->post('id'), 'word');
        }
    }

    public function changeType()
    {
    	if($_SESSION['user']->admin == 1 && $this->data->post('word') > 0 && $this->data->post('type') > 0)
    		$this->db->updateRow('wl_language_words', array('type' => $this->data->post('type')), $this->data->post('word'));
    }

    public function copy()
    {
    	if($_SESSION['user']->admin == 1 && $this->data->post('language') != '')
        {
    		$this->load->model('wl_language_model');
    		$this->wl_language_model->copy($this->data->post('alias'), $this->data->post('language'));
    	}
    }

}

?>