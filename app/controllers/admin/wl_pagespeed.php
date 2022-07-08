<?php

class wl_pagespeed extends Controller {

    public function _remap($method)
    {
        $_SESSION['alias']->name = 'Оптимізація на основі Google PageSpeed Insights';
        $_SESSION['alias']->breadcrumb = array('PageSpeed Insights' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
            $this->$method();
        else
            $this->index($method);
    }

	public function index()
	{
		if($_SESSION['user']->admin)
		{
			$res = array('UnZip' => false, 'manifest' => false);
			if($res['UnZip'] = $this->UnZip())
				$res['manifest'] = $this->read_MANIFEST();

			$this->load->admin_view('wl_optimize/pagespeed_view', $res);
		}
		else
			$this->redirect('admin');
	}

	private function UnZip($form_name = 'optimized_contents')
	{
		$res = false;
		if(isset($_FILES[$form_name]))
		{
			$path = 'optimize';
			if(!is_dir($path))
            {
                if(mkdir($path, 0777) == false)
                    exit('Error create dir ' . $path);
            }
            $file = $path.'/optimized_contents.zip';
            move_uploaded_file($_FILES[$form_name]['tmp_name'], $file);

            $zip = new ZipArchive;
			if ($zip->open($file) === TRUE)
			{
			    $zip->extractTo($path.'/');
			    $zip->close();
			    
			    $file = $path.'/MANIFEST';
				if(file_exists($file))
					return true;
			}
		}
		return false;
	}

	public function read_MANIFEST()
	{
		$files = array();
		$siteDelimiter = ': '.SITE_URL;
		$accessFolder = array('assets', 'js', 'style', 'images', 'css');
		$accessFileTypes = array('css', 'js', 'jpg', 'jpeg', 'png');
		$accessKey = array('css', 'js', 'image');
		$i = 0;

		$file = 'optimize/MANIFEST';
		$manifest = fopen($file, "r");
		while (($line = fgets($manifest)) !== false) {
	        if($i == 0)
	        {
				if(substr($line, 0, 46) == 'This zip file contains optimized resources for')
					$i++;
	        }
	        elseif($i > 0)
			{
				$row = explode('/', $line, 2);
				if(in_array($row[0], $accessKey) && isset($row[1]))
				{
					$parts = explode($siteDelimiter, $row[1]);
					if(count($parts) == 2)
					{
						$folder = explode('/', $parts[1]);
						if(in_array($folder[0], $accessFolder))
						{
							$file = array();
							$file['part'] = $row[0];
							$ext = explode('.', end($folder));
							$ext = end($ext);
							$ext = trim($ext);
							if(in_array($ext, $accessFileTypes))
							{
								$file['from'] = 'optimize/'.$row[0].'/'.$parts[0];
								if(file_exists($file['from']))
								{
									$file['to'] = trim($parts[1]);
									if(file_exists($file['to']))
									{
										$file['from_size'] = filesize($file['from']);
										$file['to_size'] = filesize($file['to']);
										array_push($files, $file);
									}
								}
							}
							else
							{
								$ext = explode('?', $ext, 2);
								if(in_array($ext[0], $accessFileTypes))
								{
									$sub = isset($ext[1]) ? strlen($ext[1]) * -1 : 0;
									$file['from'] = 'optimize/'.$row[0].'/'.$parts[0];
									if(file_exists($file['from']))
									{
										$file['to'] = trim(substr($parts[1], 0, $sub));
										if(file_exists($file['to']))
										{
											$file['from_size'] = filesize($file['from']);
											$file['to_size'] = filesize($file['to']);
											array_push($files, $file);
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $files;
	}

	public function replace()
	{
		if($_SESSION['user']->admin)
		{
			$_SESSION['notify'] = new stdClass();
			if(file_exists('optimize/MANIFEST'))
			{
				$moved = $renamed = 0;
				$date_backup = date('_d.m.Y_H.i.');
				$files = $this->read_MANIFEST();
				foreach ($files as $file) {
					if(in_array($file['to'], $_POST['replace']))
					{
						if(isset($_POST['backup']) && in_array($file['to'], $_POST['backup']))
						{
							$name = explode('.', $file['to']);
							$ext = array_pop($name);
							$name = implode('.', $name);
							$name .= $date_backup . $ext;
							if(rename($file['to'], $name))
								$renamed++;
						}
						if(rename($file['from'], $file['to']))
							$moved++;
					}
				}
				$this->data->removeDirectory('optimize');
				$_SESSION['notify']->success = "Успішно замінено <strong>{$moved}</strong> файлів. Резервні копії зроблено для <strong>{$renamed}</strong> файлів.";
			}
			else
				$_SESSION['notify']->errors = 'Файл optimize/MANIFEST не знайдено. Повторіть процедуру';
			$this->redirect();
		}
		else
			$this->redirect('admin');
	}

}

?>