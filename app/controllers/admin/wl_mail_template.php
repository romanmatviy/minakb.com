<?php

class wl_mail_template_admin extends Controller {

    function _remap($method)
    {
        $_SESSION['alias']->name = 'Емейл розсилка';
        $_SESSION['alias']->breadcrumb = array('Емейл розсилка' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db') {
            $this->$method();
        } else {
            $this->index($method);
        }
    }

    public function index()
    {

        if($this->data->uri(2) != ''){
            $mailTemplate = $this->data->uri(2);
            $mailTemplate = $this->db->getAllDataById('wl_mail_templates', $mailTemplate);
            if($mailTemplate) {
                $_SESSION['alias']->name = 'Редагувати розсилку № '.$mailTemplate->id;
                $_SESSION['alias']->breadcrumb = array('Емейл розсилка' => 'admin/wl_mail_template', 'Редагувати розсилку № '.$mailTemplate->id => '');

                $tempData = $this->db->getAllDataByFieldInArray('wl_mail_templats_data', $mailTemplate->id, 'template');
                $mailTemplateData = array();
                $fields = new stdClass();

                $allForms = $this->db->getQuery("SELECT id, name, title FROM `wl_forms` WHERE `send_mail` = 1", 'array');

                $checkedForms = $this->db->getQuery("SELECT ma.form, f.name as formName, ff.name  FROM `wl_mail_active` as ma LEFT JOIN `wl_forms` as f ON ma.form = f.id LEFT JOIN `wl_fields` as ff ON f.id = ff.form WHERE ma.template = $mailTemplate->id", 'array');
                $fields = $this->db->getQuery("SELECT ff.name, it.name as type FROM `wl_mail_active` as ma LEFT JOIN `wl_forms` as f ON ma.form = f.id LEFT JOIN `wl_fields` as ff ON f.id = ff.form LEFT JOIN `wl_input_types` as it ON ff.input_type = it.id WHERE ma.template = $mailTemplate->id", 'array');

                if(!empty($checkedForms) && !empty($allForms)){
                    foreach ($allForms as $form) {
                        foreach ($checkedForms as $checkedForm) {
                            if($checkedForm->form == $form->id){
                                $form->checked = 1;
                                break;
                            } else $form->checked = 0;
                        }
                    }
                }

                if($mailTemplate->multilanguage == 1 && !empty($_SESSION['all_languages']) && $tempData) foreach($_SESSION['all_languages'] as $lang) {
                    foreach ($tempData as $data) {
                        if($data->language == $lang){
                            $mailTemplateData[$lang] = $data;
                        }
                    }
                } else $mailTemplateData = $tempData;

                $this->load->admin_view('wl_mail_template/edit_view', array('mailTemplate' => $mailTemplate, 'mailTemplateData' => $mailTemplateData, 'allForms' => $allForms, 'fields' => $fields));
            }
        } else {
            $_SESSION['alias']->name = 'Усі розсилки';
            $_SESSION['alias']->breadcrumb = array('Емейл розсилка' => 'admin/wl_mail_template', 'Усі розсилки' => '');

            $t_data = ['template' => '#t.id'];
            if($_SESSION['language']) $t_data['language'] = $_SESSION['language'];
            $mailTemplates = $this->db->select('wl_mail_templates as t')
                                        ->join('wl_mail_templats_data', 'title', $t_data)
                                        ->get('array');

            $this->load->admin_view('wl_mail_template/index_view', array('mailTemplates' => $mailTemplates));
        }
    }

    public function add()
    {
        $_SESSION['alias']->name = 'Додати нову розсилку';
        $_SESSION['alias']->breadcrumb = array('Емейл розсилка' => 'admin/wl_mail_template', 'Нова розсилка' => '');
        $this->load->admin_view('wl_mail_template/add_view');
    }

    public function save()
    {
        if(isset($_POST['from']) && isset($_POST['to'])){
            $data = array();
            $data['from'] = $this->data->post('from');
            $data['to'] = $this->data->post('to');
            $data['multilanguage'] = $_SESSION['language'] != false ? 1 : 0;
            $data['savetohistory'] = $this->data->post('saveToHistory') ? 1 : 0;

            if(isset($_POST['mailTemplateId'])){
                $mailTemplateId = $this->data->post('mailTemplateId');

                $this->db->executeQuery("DELETE FROM `wl_mail_active` WHERE `template` = $mailTemplateId");
                if(isset($_POST['form'])){
                    foreach ($_POST['form'] as $form) {
                        $this->db->insertRow('wl_mail_active', array('form' => $form, 'template' => $mailTemplateId, 'active' => 1));
                    }
                }

                $this->db->updateRow('wl_mail_templates', $data, $mailTemplateId);
                header('Location:'.SITE_URL.'admin/wl_mail_template/'.$mailTemplateId);
            } else {
                $this->db->insertRow('wl_mail_templates', $data);
                $lastId = $this->db->getLastInsertedId();
                header('Location:'.SITE_URL.'admin/wl_mail_template/'.$lastId);
            }
        }
    }

    public function saveText()
    {
        if(isset($_POST['language']) && isset($_POST['template']) && isset($_POST['title']) && isset($_POST['text'])){
            $data = array();
            $data['language'] = $this->data->post('language');
            $data['template'] = $this->data->post('template');
            $data['title'] = $this->data->post('title');
            $data['text'] = $this->data->post('text');

            $this->db->executeQuery("SELECT * FROM `wl_mail_templats_data` WHERE `template` = '{$data['template']}' AND `language` = '{$data['language']}' ");
            if($this->db->numRows() == 0){
                $this->db->insertRow('wl_mail_templats_data', $data);
            } else {
                if(empty($data['language'])) $where = " WHERE `template` = '{$data['template']}' AND `language` = '' ";
                else $where = " WHERE `template` = '{$data['template']}' AND `language` = '{$data['language']}' ";

                $this->db->executeQuery("UPDATE `wl_mail_templats_data` SET `title` = '{$data['title']}', `text` = '{$data['text']}' $where ");
            }

            header('Location:'.$_SERVER['HTTP_REFERER']);

        }
    }

    public function history()
    {
        if(is_numeric($this->data->uri(3)))
        {
            $this->db->select('wl_mail_history', '*', $this->data->uri(3));
            $join['template'] = '#wl_mail_history.template';
            if($_SESSION['language'])
                $join['language'] = $_SESSION['language'];
            $this->db->join('wl_mail_templats_data', 'title as template_name', $join);
            if($history = $this->db->get('single'))
            {
                $_SESSION['alias']->name = 'Лист "'.$history->subject.'" '.date('d.m.Y H:i', $history->date);
                $this->load->admin_view('wl_mail_template/history_view', array('history' => $history));
            }
        }
        $this->load->page_404(false);
    }

    public function deleteTemplate()
    {
        $id = $this->data->post('id');

        if($id)
        {
            $this->db->deleteRow('wl_mail_templates', $id);
            $this->db->deleteRow('wl_mail_active', $id, 'template');
            $this->db->deleteRow('wl_mail_templats_data', $id, 'template');
            $this->db->deleteRow('wl_mail_history', $id, 'template');
        }
    }
}

?>