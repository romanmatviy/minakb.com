<?php

/*
 * Шлях: SYS_PATH/libraries/image.php
 *
 * Робота з зображеннями
 * Версія 1.0.1 (15.06.2013) - додано uploadArray(), wh_size(), cut()
 * Версія 1.0.2 (08.10.2013) - додано preview(), setExtension(), resize(+enlarge)
 * Версія 1.0.3 (16.10.2013) - додано delete(), 25.10.2013 - додано підтримку роботи з розширеннями png i gif
 * Версія 1.0.3+ (26.10.2013) - додано getExtension(), виправлено preview(), resize(), save()
 * Версія 1.0.4 (27.12.2013) - додано/виправлено у resize() правильне зменшення тільки по ширині зображення
 * Версія 1.0.5 (27.11.2015) - виправлено помилку при додаванні фотографій за короткою адресою сайту
 * Версія 1.1 (13.01.2017) - додано підтримку прозорості зображень при зміні розміру
 * Версія 1.2 (07.02.2017) - додано підтипи для змін розміру зображень, покращено заливку та збереження фото
 * Версія 1.3 (24.11.2018) - додано можливість (якщо сервер дозволяє php.ini)/повідомлення про молку для заливки фотографій великого розміру
 * Версія 1.3.1 (11.12.2019) - виправлено збереження файлів у jpeg форматі
 * Версія 1.4 (30.06.2020) - додано підтримку svg
 * Версія 1.4.1 (15.10.2020) - додано setName(), getName(). return upload/uploadArray()
 * Версія 1.4.2 (14.12.2020) - setExtension($ext = NULL) => $this->extension = false; *для коректної роботи uploadArray
 * Версія 1.4.3 (25.12.2020) - add destroy()
 * Версія 1.5   (04.01.2021) - fix Orientation when upload (read exif data). add rotare()
 * 1.5.1 	25.03.2021	support webp
 */

class Image {

    private $image;
    private $path;
    private $name;
    private $quality = 100;
    private $type;
	private $extension = false;
    private $allowed_ext = array('png', 'jpg', 'jpeg', 'gif', 'svg', 'webp');
    private $upload_types = array('image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/webp');
    private $max_size = 52428;
    private $errors = array();
	
	/*
     * Примусове задання назви зображення
	 * Задавати перед save()
     */
	public function setName($name)
	{
		if(!empty($name))
			$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}
	
	/*
     * Примусове задання розширення зображення
	 * Задавати перед upload(), uploadArray(), save(), перед/після loadImage()
     */
	public function setExtension($ext = NULL)
	{
		if($ext === NULL)
		{
			$this->extension = false;
			return true;
		}
		if(in_array($ext, $this->allowed_ext))
		{
			$this->extension = $ext;
			return true;
		}
		return false;
	}
	
	/*
	 * Вертає поточне розширення зображення
	 */
	public function getExtension()
	{
		if($this->image || $this->extension == 'svg')
			return $this->extension;
		return false;
	}

    /*
     * Завантаження зображення з файлової системи
	 * $filepath - адреса папки звідки слід взяти зображення (може включати абсолютний шлях серверу)
	 * $name - назва зображення (без розширення)
	 * $extension - розширення зображення (по замовчуванню jpg)
     */
    public function loadImage($filepath, $name = '', $extension = 'jpg', $fixOrientation = false)
    {
        if($name != '')
			$fullpath = $filepath.$name.'.'.$extension;
        else
        {
        	$fullpath = $filepath;
        	$filepath = explode('/', $filepath);
            $name = array_pop($filepath);
            $filepath = implode('/', $filepath);
        	$name = explode('.', $name);
        	$extension = array_pop($name);
			$name = implode('.', $name);
        }

        if(in_array($extension, $this->allowed_ext) == false)
			return false;
		if($extension == 'svg')
		{
			$this->extension = 'svg';
			return true;
		}
		
		// Функція потребує NULL_PATH (відносно index.php)
		// !Важливо для роботи через піддомен
        @$info = getimagesize($fullpath);
		
		if(!empty($info))
		{
			$MB = 1048576;  // number of bytes in 1M
		    $K64 = 65536;    // number of bytes in 64K
		    $TWEAKFACTOR = 1.5;  // Or whatever works for you
		    if(empty($info['bits']))
		    	$info['bits'] = 32;
		    if(empty($info['channels']))
		    	$info['channels'] = 1;
		    $memoryNeeded = round( ( $info[0] * $info[1] * $info['bits'] * $info['channels'] / 8 + $K64 ) * $TWEAKFACTOR );
		    //ini_get('memory_limit') only works if compiled with "--enable-memory-limit" also
		    //Default memory limit is 8MB so well stick with that. 
		    //To find out what yours is, view your php.ini file.
		    $memoryLimit = 16 * $MB;
		    if($memory_limit = ini_get('memory_limit'))
		    {
		    	$size = intval($memory_limit);
		    	switch (substr($memory_limit, -1)) {
		    		case 'M':
		    		case 'm':
		    			$memoryLimit = $size * $MB;
		    			break;
		    		case 'K':
		    		case 'k':
		    			$memoryLimit = $size * 1024;
		    			break;
		    		
		    		default:
		    			$memoryLimit = $size;
		    			break;
		    	}
		    }
		    if (function_exists('memory_get_usage') && memory_get_usage() + $memoryNeeded > $memoryLimit) 
		    {
		        $newLimit = $memoryLimit + ceil( ( memory_get_usage() + $memoryNeeded - $memoryLimit + $MB) / $MB );
		        if(!ini_set( 'memory_limit', $newLimit . 'M' ))
		        {
		        	$memoryLimit /= $MB;
		        	$this->errors[] = 'SERVER MEMORY_LIMIT '.ceil( $memoryLimit ).'Mb. You need limit not less '.$newLimit.'Mb';
		        	return false;
		        }
		    }
		    elseif($memoryNeeded > $memoryLimit)
		    {
		    	$newLimit = ceil( ($memoryNeeded - $memoryLimit + $MB) / $MB );
		    	$this->errors[] = 'You need SERVER MEMORY_LIMIT not less '.$newLimit.'Mb';
		        return false;
		    }

			$this->type = $info[2];
			$this->path = $filepath;
			$this->name = $name;
			
			switch ($this->type){
				case '1' :
					$fixOrientation = false;
					$this->image = imagecreatefromgif($fullpath);
					imagealphablending($this->image, false);
					imagesavealpha($this->image, true);
					if($this->extension == false) $this->extension = 'gif';
					break;
				case '2' :
					$this->image = imagecreatefromjpeg($fullpath);
					if($this->extension == false)
					{
						if($extension == 'jpeg')
							$this->extension = 'jpeg';
						else
							$this->extension = 'jpg';
					}
					break;
				case '3' :
					$fixOrientation = false;
					$this->image = imagecreatefrompng($fullpath);
					imagealphablending($this->image, false);
					imagesavealpha($this->image, true);
					if($this->extension == false) $this->extension = 'png';
					break;
				case '18' :
					$fixOrientation = false;
					$this->image = imagecreatefromwebp($fullpath);
					imagealphablending($this->image, false);
					imagesavealpha($this->image, true);
					if($this->extension == false) $this->extension = 'webp';
					break;
			}

			if($fixOrientation)
			{
				$exif = exif_read_data($fullpath);
				$angles = array(8 => 90, 3 => 180, 6 => -90);
				if(!empty($exif['Orientation']) && isset($angles[$exif['Orientation']]))
				{
				    $this->image = imagerotate($this->image, $angles[$exif['Orientation']], 0);
				    $this->save('', '', false);
				}
			}
			
			return true;
		}
		return false;
    }

    /*
     * Завантаження зображення зі сторони клієнта
	 * $img_in - назва поля у POST запиті ($_FILES[$img_in])
	 * $img_out - адреса папки куди слід відвантажити зображення (шлях відносно кореня сайту)
	 * $name - назва збереженого зображення. Якщо не задано, то оригінальна назва зображення
     */
    public function upload($img_in, $img_out, $name = '')
    {
		if(is_uploaded_file($_FILES[$img_in]['tmp_name']))
		{
            $pos = strrpos($_FILES[$img_in]['name'], '.');
            if($pos)
            {
                $name_length = strlen($_FILES[$img_in]['name']) - $pos;
                $ext = strtolower(substr($_FILES[$img_in]['name'], $pos + 1, $name_length));
                if(in_array($ext, $this->allowed_ext))
                {
                    if(in_array($_FILES[$img_in]['type'], $this->upload_types) || $ext == 'svg')
                    {
                        $size = $_FILES[$img_in]['size'] / 1024;
                        if($size <= $this->max_size)
                        {
							if($this->extension) $ext = $this->extension;
							if($name == '') $name = stripslashes(substr($_FILES[$img_in]['name'], 0, $pos - 1));
                            $path = $img_out.$name.'.'.$ext;
                            move_uploaded_file($_FILES[$img_in]['tmp_name'], $path);
                            return $this->loadImage($img_out, $name, $ext, true);
                        }
                        else
                        {
                            array_push($this->errors, 'Розмір файлу не повинен перевищувати '.$this->max_size);
                            return false;
                        }
                    }
                    else
                    {
                        array_push($this->errors, 'Такий тип файлу  не підтримується.');
                        return false;
                    }
                }
                else
                {
                    array_push($this->errors, 'Таке розширення не підтримується.');
                    return false;
                }
            }
            else
            {
                array_push($this->errors, 'Файл повинен мати розширення.');
                return false;
            }
        }
    }
	
	/*
     * Обробка масового (групового) завантаження зображень зі сторони клієнта
	 * $img_in - назва поля у POST запиті ($_FILES[$img_in])
	 * $i - номер елементу у масиві $_FILES[$img_in]['tmp_name'][$i]
	 * $img_out - адреса папки куди слід відвантажити зображення (шлях відносно кореня сайту)
	 * $name - назва збереженого зображення. Якщо не задано, то оригінальна назва зображення
     */
	public function uploadArray($img_in, $i, $img_out, $name = '')
	{
        if(is_uploaded_file($_FILES[$img_in]['tmp_name'][$i]))
        {
            if($pos = strrpos($_FILES[$img_in]['name'][$i], '.'))
            {
                $name_length = strlen($_FILES[$img_in]['name'][$i]) - $pos;
                $ext = strtolower(substr($_FILES[$img_in]['name'][$i], $pos + 1, $name_length));
                if(in_array($ext, $this->allowed_ext))
                {
                    if(in_array($_FILES[$img_in]['type'][$i], $this->upload_types) || $ext == 'svg')
                    {
                        $size = $_FILES[$img_in]['size'][$i] / 1024;
                        if($size <= $this->max_size)
                        {
							if($this->extension) $ext = $this->extension;
							if($name == '') $name = stripslashes(substr($_FILES[$img_in]['name'], 0, $pos - 1));
                            $path = $img_out.$name.'.'.$ext;
                            move_uploaded_file($_FILES[$img_in]['tmp_name'][$i], $path);
                            return $this->loadImage($img_out, $name, $ext, true);
                        }
                        else
                        {
                            array_push($this->errors, 'Розмір файлу не повинен перевищувати '.$this->max_size);
                            return false;
                        }
                    }
                    else
                    {
                        array_push($this->errors, 'Такий тип файлу не підтримується.');
                        return false;
                    }
                }
                else
                {
                    array_push($this->errors, 'Таке розширення не підтримується.');
                    return false;
                }
            }
            else
            {
                array_push($this->errors, 'Файл повинен мати розширення.');
                return false;
            }
        }
    }
	
	/*
	 * Функція повертає розміри (розширення) зображення у px
	 */ 
	public function wh_size()
	{
		if($this->image)
		{
			$size = array();
			$size['width'] = imagesx($this->image);
			$size['height'] = imagesy($this->image);
			$size['w'] = $size['width'];
			$size['h'] = $size['height'];
			return $size;
		}
		return null;
	}
	
	/*
     * Створення мініатюри зображення
	 * Функція змінює розміри зображення до максимально можливого, опісля центрує та обрізає зображення. На виході мініатюра заданого розміру.
	 * $quality - якість кінцевого зображення після обробки у відсотках
	 * $type - режим роботи: 2 - центрувати, 21 - зберігає верхній лівий край, 22 - зберігає правий нижній край.
     */
    public function preview($width, $height, $quality = 100, $type = 2)
    {
		if($this->image)
		{
			$this->quality = $quality;
			
			$src_w = imagesx($this->image);
			$src_h = imagesy($this->image);

			$w = $width;
			$h = $height;
			$ratio = $src_w / $src_h;
			if($width / $ratio < $height)
				$w = round($height * $ratio) + 1;
			else
				$h = round($width / $ratio) + 1;
			
			$this->resize($w, $h, $quality, 1, true);

			$src = $this->image;
			$w = imagesx($src);
			$h = imagesy($src);
			$this->image = imagecreatetruecolor($width, $height);
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);
			
			$src_x = $src_y = 0;
			if($type == 2)
			{
				if($w > ($width - 1) && $w <= ($width + 1))
					$src_y = ($h - $height) / 2;
				else
					$src_x = ($w - $width) / 2;
			}
			if($type == 22)
			{
				if($w > ($width - 1) && $w <= ($width + 1))
					$src_y = $h - $height;
				else
					$src_x = $w - $width;
			}
			
			imagecopy ($this->image, $src, 0, 0, $src_x, $src_y, $width, $height);
			
			imagedestroy($src);
			return true;
		}
        return false;
    }

    /*
     * Зміна розмірів зображення
     * $type - режим роботи: 1 - авто по довшій стороні; 11 - фіксована ширина, висота змінна; 12 - фіксована висота, ширина змінна
	 	22 - залишає право низ.
     */
    public function resize($width, $height = 0, $quality = 100, $type = 1, $enlarge = false)
    {
		if($this->image)
		{
			$this->quality = $quality;
			$src = $this->image;
			$src_w = imagesx($src);
			$src_h = imagesy($src);

			if($enlarge || $width < $src_w || $height < $src_h)
			{
				$ratio = $src_w / $width;
				if($src_w < $src_h && $height > 0)
					$ratio = $src_h / $height;
				$dest_w = round($src_w / $ratio);
				$dest_h = round($src_h / $ratio);
				if($dest_h > $height && $height > 0) 
					$dest_h = $height;
				if($type == 11)
				{
					$ratio = $src_w / $width;
					$dest_w = $width;
					$dest_h = round($src_h / $ratio);
				}
				if($type == 12 && $height > 0)
				{
					$ratio = $src_h / $height;
					$dest_w = round($src_w / $ratio);
					$dest_h = $height;
				}

				$this->image = imagecreatetruecolor($dest_w, $dest_h);
				imagealphablending($this->image, false);
				imagesavealpha($this->image, true);
				imagecopyresampled($this->image, $src, 0, 0, 0, 0, $dest_w, $dest_h, $src_w, $src_h);
				
				imagedestroy($src);
				return true;
			}
		}
        return false;
    }
	
	 /*
     * Обрізання зображення
     */
    public function cut($width, $height, $red = 0, $green = 0, $blue = 0)
    {
		if($this->image)
		{
			$src = $this->image;
			$this->image = imagecreatetruecolor($width, $height);
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);
			if($red > 0 || $green > 0 || $blue > 0)
			{
				$color = imagecolorallocate ($this->image, $red, $green, $blue);
				imagefill($this->image, 0, 0, $color);
			}
			imagecopy($this->image, $src, 0, 0, 0, 0, $width, $height);
			imagedestroy($src);
			return true;
		}
        return false;
    }

    public function rotate($angle=0, $bg = 0)
    {
    	if($this->image)
    	{
    		$this->image = imagerotate($this->image, $angle, $bg);
    		return true;
    	}
    	return false;
    }

    /*
     * Зберігання отриманого зображення
     */
    public function save($prefix = '', $path = '', $destroy = true)
    {
		if($this->image)
		{
			if($path == '')
				$path = $this->path.'/';
			$name = ($prefix != '') ? $prefix.'_'.$this->name.'.'.$this->extension : $this->name.'.'.$this->extension;

			if($this->extension == 'gif')
			{
				if(imagegif($this->image, $path.$name)){
					if($destroy)
						imagedestroy($this->image);
					return true;
				}
			}
			if($this->extension == 'jpg' || $this->extension == 'jpeg')
			{
				if(imagejpeg($this->image, $path.$name, $this->quality)){
					if($destroy)
						imagedestroy($this->image);
					return true;
				}
			}
			if($this->extension == 'png')
			{
				if(imagepng($this->image, $path.$name)){
					if($destroy)
						imagedestroy($this->image);
					return true;
				}
			}
			if($this->extension == 'webp')
			{
				if(imagewebp($this->image, $path.$name, 99)){
					if($destroy)
						imagedestroy($this->image);
					return true;
				}
			}
			
			if($destroy)
				imagedestroy($this->image);
		}
        return false;
    }

    public function destroy()
    {
    	if($this->image)
    	{
    		imagedestroy($this->image);
    		return true;
    	}
    	return false;
    }
	
	/*
     * Видалення зображення
     */
	public function delete($path = '')
	{
		$path = ($path != '') ? $path : $this->path;
		if(file_exists($path))
		{
			if(unlink($path))
				return true;
		}
		return false;
	}

    /*
     * Отримує помилки
     */
    public function getErrors($open_tag = '<p>', $closed_tag = '</p>')
    {
        $errors = '';
        foreach ($this->errors as $error) {
            $errors .= $open_tag.$error.$closed_tag;
        }
        return $errors;
    }
}

?>