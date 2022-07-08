<?php

/*
 * Версія 2.0 (05.03.2019) підключено smtp через Swift_Mailer
 * Версія 2.1 (22.10.2019) додано fromName(, addAttach() для Swift_Mailer
 * Версія 2.2 (10.05.2020) додано addToSchedule() - відкладену відправку листів
 * Версія 2.2.1 (17.11.2020) змінено $this->to() - мультиемейл відправка
 * Версія 2.2.2 (27.11.2020) fix $this->to()
 */

class Mail extends Controller {

    private $template = 0; // for wl_forms
    private $to;
    private $from;
    private $fromName = '';
    private $subject;
    private $replyTo;
    private $message;
    private $params;
    private $attach = [];
    private $smtp;

    /*
     * Отримуємо дані для з'єднання з конфігураційного файлу
     */
    function __construct($cfg)
    {
        if(!empty($cfg['host']) && $cfg['host'] != '$MAILHOST')
        {
            $cfg['port'] = $cfg['port'] ?? 25;
            $cfg['encryption'] = $cfg['encryption'] ?? null;
            require_once 'swiftmailer/autoload.php';

            $transport = (new Swift_SmtpTransport($cfg['host'], $cfg['port'], $cfg['encryption']))
              ->setUsername($cfg['user'])
              ->setPassword($cfg['password']);

            $this->from = $this->replyTo = $cfg['user'];

            $this->smtp = new Swift_Mailer($transport);
        }
    }

    public function template($template)
    {
        if(is_numeric($template))
            $this->template = $template;
    }

    public function from($send_from)
    {
        if(empty($this->smtp))
        {
            $this->from = $send_from;
            $this->replyTo = $send_from;
        }
    }

    public function fromName($fromName)
    {
        $this->fromName = $fromName;
    }

    public function to($send_to)
    {
        $this->to = [];
        $send_to = str_replace([',', ' ', ';'], ',', $send_to);
        foreach (explode(',', $send_to) as $to) {
            $to = trim($to);
            if(!empty($to))
                $this->to[] = $to;
        }
    }

    public function replyTo($replyTo)
    {
        $this->replyTo = $replyTo;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
    }

    public function message($message)
    {
        $this->message = $message;
    }

    public function params($pms)
    {
        $this->params = $pms;
        $this->params['SITE_URL'] = SITE_URL;
        $this->params['SERVER_URL'] = SERVER_URL;
        $this->params['IMAGE_PATH'] = IMG_PATH;
        $this->params['SITE_NAME'] = SITE_NAME;
    }

    public function addAttach($file_path, $file_name = false)
    {
        if($file_name)
            $this->attach[$file_path] = $file_name;
        else
            $this->attach[] = $file_path;
    }

    public function attachArray($attach)
    {
        if(!empty($attach) && is_array($attach))
            $this->attach = array_merge($this->attach, $attach);
    }

    public function send($force = false, $saveToHistory = false)
    {
        if(is_array($this->params))
            foreach($this->params as $key => $value) {
                $this->subject = str_replace('{'.$key.'}', $value, $this->subject);
                $this->message = str_replace('{'.$key.'}', $value, $this->message);
            }

        $headers ="Mime-Version: 1.0 \r\n";
        $headers .= "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "From: ".SITE_NAME." <".$this->from."> \r\n";
        $headers .= 'Reply-To: '.$this->replyTo;

        $sent_mail = new stdClass();
        if($this->fromName)
            $sent_mail->from = $this->fromName .' '.$this->from;
        else
            $sent_mail->from = $this->from;
        $sent_mail->to = implode(', ', $this->to);
        $sent_mail->replyTo = $this->replyTo;
        $sent_mail->subject = $this->subject;
        $sent_mail->message = $this->message;
        $sent_mail->headers = $headers;

        if($_SESSION['option']->sendEmailForce || $force)
        {
            if($this->smtp)
            {
                $from = $this->from;
                if($this->fromName)
                    $from = [$this->from => $this->fromName];
                $message = (new Swift_Message($this->subject))
                          ->setFrom($from)
                          ->setTo($this->to)
                          ->setBody($this->message, 'text/html');
                if($this->replyTo)
                    $message->setReplyTo($this->replyTo);

                if($this->attach)
                    foreach ($this->attach as $file_path => $file_name) {
                        if(is_numeric($file_path))
                        {
                            if(file_exists($file_name))
                                $message->attach( Swift_Attachment::fromPath($file_name) );
                        }
                        else
                        {
                            if(file_exists($file_path))
                                $message->attach( Swift_Attachment::fromPath($file_path)->setFilename($file_name) );
                        }
                    }

                if($this->smtp->send($message))
                {
                    if($saveToHistory)
                        $this->addToSchedule(true);
                    return $sent_mail;
                }
            }
            else
            {
                if($saveToHistory)
                    $this->addToSchedule(true);
                if($_SERVER["SERVER_NAME"] == 'localhost')
                    return $sent_mail;
                mail(implode(', ', $this->to), $this->subject, $this->message, $headers);
            }
        }
        else
            $this->addToSchedule();
        return $sent_mail;
    }

	public function sendTemplate($template, $to, $data = array(), $force = false, $saveToHistory = false)
    {
		$path = APP_PATH.'mails'.DIRSEP.$template.'.php';
        if($_SESSION['language'])
        {
            $path = APP_PATH.'mails'.DIRSEP.$_SESSION['language'].DIRSEP.$template.'.php';
            if(file_exists($path) == false)
                $path = APP_PATH.'mails'.DIRSEP.$template.'.php';
        }
        if($_SESSION['alias']->service)
        {
            $folder_path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'mails';
            if(is_dir($folder_path))
            {
                $folder_path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'mails'.DIRSEP.$template.'.php';
                if(file_exists($folder_path))
                {
                    $path = $folder_path;
                    if($_SESSION['language'])
                    {
                        $folder_path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'mails'.DIRSEP.$_SESSION['language'].DIRSEP.$template.'.php';
                        if(file_exists($folder_path))
                            $path = $folder_path;
                    }
                }
            }
            
        }
		if(file_exists($path))
        {
			$subject = '';
			$message = '';
            $from_mail = SITE_EMAIL;
            $from_name = 'Адміністрація '.SITE_NAME;
			require($path);
			if($message != '' && $subject != '')
            {
				$this->params(array('name' => $from_name));
				$this->message(html_entity_decode($message));
				$this->subject($subject);
				$this->to($to);
				$this->from($from_mail);

				return $this->send($force, $saveToHistory);
			}
		}
		return false;
	}

    public function sendMailTemplate($template, $data = array(), $force = false, $saveToHistory = false)
    {
        $from = $this->checkMail($template->from, $data);
        $to = $this->checkMail($template->to, $data);

        if($from && $to)
        {
            $this->params($data);
            $this->message(html_entity_decode($template->message));
            $this->subject($template->subject);
            $this->to($to);
            $this->from($from);
            if(!empty($template->template))
                $this->template($template->template);
            if(!empty($template->attach))
                $this->attachArray($template->attach);

            return $this->send($force, $saveToHistory);
        }
        return false;
    }

    public function checkMail($mail, $data = array())
    {
        switch ($mail) {
            case 'SITE_EMAIL':
                return SITE_EMAIL;
                break;
            case '{SITE_EMAIL}':
                return SITE_EMAIL;
                break;

            default:
                if(substr($mail, 0, 1) == '{' && substr($mail, -1) == '}')
                    $mail = substr($mail, 1, -1);
                if(!isset($data[$mail]) && preg_match('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})^', $mail))
                    return $mail;
                elseif(isset($data[$mail]) && preg_match('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})^', $data[$mail]))
                    return $data[$mail];
                else
                    return false;
                break;
        }
        return false;
    }

    public function addToSchedule($send = false)
    {
        $this->library('db');
        $mail = [];
        $mail['template'] = $this->template;
        $mail['date'] = time();
        $mail['from'] = $this->from;
        $mail['fromName'] = $this->fromName;
        $mail['to'] = implode(', ', $this->to);
        $mail['replyTo'] = $this->replyTo;
        $mail['subject'] = $this->subject;
        $mail['message'] = $this->message;
        $mail['attach'] = serialize($this->attach);
        $mail['send_email'] = ($send) ? 1 : 0;
        $this->db->insertRow('wl_mail_history', $mail);
    }

}
?>
