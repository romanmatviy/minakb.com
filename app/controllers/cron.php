<?php

// White Lion 1.2
// Cron functions

class cron extends Controller {

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
        $this->redirect('/');
    }

    public function mail()
    {
        $this->load->library('mail');
        if($mails = $this->db->select('wl_mail_history as h', '*', ['send_email' => 0])
                                ->join('wl_mail_templates', 'savetohistory', '#h.template')
                                ->limit(8)
                                ->get('array'))
        {
            $time = time();
            foreach ($mails as $mail) {
                $this->mail->from($mail->from);
                $this->mail->fromName($mail->fromName);
                $this->mail->to($mail->to);
                $this->mail->replyTo($mail->replyTo);
                $this->mail->subject($mail->subject);
                $this->mail->message($mail->message);
                $this->mail->attachArray(unserialize($mail->attach));
                if($this->mail->send(true))
                {
                    if($mail->savetohistory || $_SESSION['option']->sendEmailSaveHistory)
                        $this->db->updateRow('wl_mail_history', ['date' => $time, 'send_email' => 1], $mail->id);
                    else
                        $this->db->deleteRow('wl_mail_history', $mail->id);
                    echo "Successfully send mail #{$mail->id} <br>";
                }
                else
                    echo "Error send mail #{$mail->id} <br>";
            }
        }
        exit;
    }

    public function __get_Search($content = 0)
    {
    	return false;
    }

    public function __get_SiteMap_Links()
    {
        return false;
    }

}

?>