<?php

 // v1 for create sitemap by cron job

class sitemap_cron extends Controller
{

	public function index()
	{
        $this->load->page_404(false);
	}

	public function update_sitemap()
	{
        $path = APP_PATH.'controllers'.DIRSEP.'admin'.DIRSEP.'wl_sitemap.php';
        require $path;
        $wl_sitemap_controller = new wl_sitemap(false);
        //start_generate() метод потребує перевірки яка йде з інтерфейсу.
        //задаємо ці параметри вручну
        $_POST['code_hidden']='111';
        $_POST['code_open']='111';
        $wl_sitemap_controller->start_generate();
	}

}

 ?>