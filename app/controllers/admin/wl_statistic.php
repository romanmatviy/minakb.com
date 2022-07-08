<?php

class wl_statistic_admin extends Controller {
				
    public function _remap($method)
    {
        $_SESSION['alias']->name = 'Статистика сайту';
        $_SESSION['alias']->breadcrumb = array('Статистика' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
            $this->$method();
        else
            $this->index($method);
    }

    public function index()
    {
        $this->load->model('wl_analytic_model');
        $this->load->admin_view('wl_statistic/index_view', array('wl_statistic' => $this->wl_analytic_model->getStatistic()));
    }

}

?>