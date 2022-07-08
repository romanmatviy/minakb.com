<?php

class newsletter extends Controller {

    function _remap($method)
    {
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->index($method);
        }
    }

    public function index()
    {
    	$this->load->page_404(false);
    }

    public function sendmail()
    {
        // echo "disabled";
        // exit;
        $this->wl_alias_model->setContent(1);
        if($logs = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_log', ['emails_count' => '>emails_sent']))
        {
            $this->load->smodel('newsletter_model');
            if($template = $this->newsletter_model->get_template($logs[0]->template))
            {
                if($logs[0]->all_users == 1)
                    $this->newsletter_model->all_users = true;
                if($mails = $this->newsletter_model->getListActiveMails($template->to_user_types, $logs[0]->emails_sent))
                {
                    $emails_sent = $logs[0]->emails_sent + $_SESSION['option']->sent_per_part;
                    if($emails_sent > $logs[0]->emails_count)
                        $emails_sent = $logs[0]->emails_count;
                    $this->db->updateRow($_SESSION['service']->table.'_log', ['emails_sent' => $emails_sent], $logs[0]->id);

                    echo date('d.m.Y H:i') .' <hr>';
                    $this->load->library('mail');
                    foreach ($mails as $mail) {
                        echo $mail->email .' <br>';
                        $mail->registered = date('d.m.Y H:i', $mail->registered);
                        $msg_body = '<html><head></head><body>'.html_entity_decode($template->text).'</body></html>';
                        $this->mail->params(array('id' => $mail->id, 'name' => $mail->name, 'email' => $mail->email, 'registered' => $mail->registered, 'date' => date('d.m.Y'), 'dateTime' => date('d.m.Y H:i'), 'auth_id' => $mail->auth_id));
                        $this->mail->message($msg_body);
                        $this->mail->subject($template->theme);
                        $this->mail->fromName($template->from);
                        $this->mail->to($mail->email);
                        if($template->files)
                            foreach ($template->files as $file) {
                                $path = 'files/'.$_SESSION['alias']->alias.'/'.$file->id;
                                if(file_exists($path))
                                    $this->mail->addAttach($path, $file->name);
                            }
                        $this->mail->send();
                    }

                    $this->db->updateRow($_SESSION['service']->table.'_templates', array('last_do' => time()), $template->id);
                }
            }
        }
    }
}

?>