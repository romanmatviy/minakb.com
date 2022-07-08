<?php

class wl_forms_admin extends Controller {

    function _remap($method)
    {
        $_SESSION['alias']->name = 'Форми';
        $_SESSION['alias']->breadcrumb = array('Форми' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db') {
            $this->$method();
        } else {
            $this->index($method);
        }
    }

    public function index()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        if($this->data->uri(2) != ''){
            $form = $this->data->uri(2);
            $form = $this->db->getAllDataById('wl_forms', $form, 'name');
            if($form){
                $field_name = $this->data->uri(3);
                if($field_name != ''){
                    $field_name = $this->db->getQuery("SELECT f.*, t.options as type_options FROM wl_fields as f LEFT JOIN wl_input_types as t ON t.id = f.input_type WHERE f.name = '{$field_name}' AND f.form = $form->id");
                    if($field_name){
                        if($field_name->type_options) $field_name->options = $this->db->getAllDataByFieldInArray('wl_fields_options', $field_name->id, 'field');

                        $fields = $this->db->getAllDataByFieldInArray('wl_fields', $form->id, 'form');

                        if($fields){
                            foreach ($fields as $f) {
                                $names[] = $f->name;
                            }
                            $diff_name = implode('","', array_diff($names, array($field_name->name)));
                        }
                        $this->load->admin_view('wl_forms/edit_field_view', array('field_name' => $field_name, 'form' => $form, 'diff_name' => $diff_name));
                    }
                    else $this->load->page_404();
                } else {
                    $_SESSION['alias']->name = 'Редагувати форму';
                    $_SESSION['alias']->breadcrumb = array('Форми' => 'admin/wl_forms', 'Редагувати' => '');

                    $fields = $this->db->getQuery("SELECT f.*, i.name as input_type_name FROM wl_fields as f LEFT JOIN wl_input_types as i ON i.id = f.input_type WHERE f.form = {$form->id}", 'array');
                    $names = array();

                    if($fields){
                        foreach ($fields as $f) {
                            $names[] = $f->name;
                        }
                        $names = implode('","', $names);
                    }

                    $checkedTemplates = $this->db->getQuery("SELECT template FROM `wl_mail_active` WHERE `form` = $form->id", 'array');

                    $allTemplates = $this->db->getQuery("SELECT t.id, td.title FROM `wl_mail_templates` as t LEFT JOIN `wl_mail_templats_data` as td ON t.id = td.template ", 'array');

                    $templates = array();
                    $known = array();
                    if(!empty($allTemplates)){
                        $templates = array_filter($allTemplates, function ($val) use (&$known) {
                            $unique = !in_array($val->id, $known);
                            $known[] = $val->id;
                            return $unique;
                        });
                    }

                    if(!empty($checkedTemplates)){
                        foreach ($templates as $template) {
                            foreach ($checkedTemplates as $checkedTemplate) {
                                if($checkedTemplate->template == $template->id){
                                    $template->checked = 1;
                                    break;
                                } else $template->checked = 0;
                            }
                        }
                    }

                    if($form->success == 2 && $_SESSION['all_languages']){
                        $form->success_data = json_decode($form->success_data);
                    }

                    $tableExist = $this->db->getQuery("SHOW TABLES LIKE '{$form->table}'");

                    $this->load->admin_view('wl_forms/edit_view', array('form' => $form, 'fields' => $fields, 'templates' => $templates, 'names' => $names, 'tableExist' => $tableExist));
                }
            } else $this->load->page_404();
        } else {
            $forms = $this->db->getAllData('wl_forms');
            $this->load->admin_view('wl_forms/list_view', array('forms' => $forms));
        }
    }

    public function add()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        $_SESSION['alias']->name = 'Додати нову форму';
        $_SESSION['alias']->breadcrumb = array('Форми' => 'admin/wl_forms', 'Нова форма' => '');
        $this->load->admin_view('wl_forms/add_view');
    }

    public function add_save()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        if(!empty($_POST)){
            if(!empty($_POST['name'])) $name = $this->data->post('name');
            $captcha = ($_POST['captcha'] == 'yes' ) ? 1 : 0;
            $title = $this->data->post('title');
            if(!empty($_POST['type'])) $type = ($_POST['type'] == 'get') ? 1 : 2;

            if(isset($name,$type)){
                $this->db->executeQuery("INSERT INTO `wl_forms` (`id` ,`name` ,`captcha` ,`title` ,`table` ,`type` ,`type_data`, `send_mail`, `send_sms`, `sidebar`) VALUES (NULL ,  '$name',  $captcha,  '$title',  '$name',  $type,  2, 0, 0, 0)");
                header("Location: ".SITE_URL."admin/wl_forms/".$name);
                exit();
            }
        }
    }

    public function add_field()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        if(!empty($_POST))
        {
            $form = $this->data->post('form');
            $formName = $this->data->post('form_name');
            $formTable = $this->data->post('form_table');

            //Провірка на унікальне ім'я
            $formById = $this->db->getAllDataByFieldInArray('wl_fields', $form, 'form');
            $namesById = array();
            if($formById)
            {
                foreach ($formById as $names) {
                    $namesById[] = $names->name;
                }
            }
            if(!in_array($_POST['name'], $namesById) && !empty($_POST['name']))
                $name = $this->data->post('name');

            $input_type = $this->data->post('input_type');
            $required = ($_POST['required'] == '1') ? 1 : 0;
            $title = $this->data->post('title');

            $lastColumn = end($namesById);
            $this->db->executeQuery("ALTER TABLE `{$formTable}` ADD {$name} text AFTER `{$lastColumn}`");

            if(isset($name, $input_type, $title))
            {
                $this->db->executeQuery("INSERT INTO `wl_fields` (`id`, `form`, `name`, `input_type`, `required`, `title`) VALUES (NULL, $form, '$name', $input_type, $required, '$title')");

                if(!empty($_POST['value']))
                {
                    $fieldId = $this->db->getLastInsertedId();
                    foreach ($_POST['value'] as $value) {
                        if(!empty($value)) $this->db->executeQuery("INSERT INTO `wl_fields_options` (`field`, `value`, `title`) VALUES($fieldId, '$value', '$value')");
                    }
                }
            }
            header("Location: ".SITE_URL."admin/wl_forms/".$formName);
        }
    }

    public function edit_field($value='')
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        if(!empty($_POST))
        {
            $id = $this->data->post('id');
            $form_id = $this->data->post('form_id');
            
            $inputs = array('input_type', 'title', 'required');
            $data = $this->data->prepare($inputs);
            
            $old_name = '';
            //Провірка на унікальне ім'я
            $formFieldsById = $this->db->getAllDataByFieldInArray('wl_fields', $form_id, 'form');
            foreach ($formFieldsById as $names) {
                $namesById[] = $names->name;
                if($names->id == $id)
                    $old_name = $names->name;
            }
            if(!in_array($_POST['name'], array_diff($namesById, array($old_name))) && !empty($_POST['name']))
                $data['name'] = $this->data->post('name');

            if(!empty($data['name']))
            {
                $this->db->updateRow('wl_fields', $data, $id);

                $form = $this->db->getAllDataById('wl_forms', $form_id);

                if($old_name != $data['name'] && !empty($form->table))
                    $this->db->executeQuery("ALTER TABLE `{$form->table}` CHANGE `{$old_name}` `{$data['name']}` text");

                $this->db->deleteRow('wl_fields_options', $id, 'field');
                if(!empty($_POST['value']))
                {
                    $option = array('field' => $id);
                    foreach ($_POST['value'] as $value) {
                        if(!empty($value))
                        {
                            $option['value'] = $option['title'] = $value;
                            $this->db->insertRow('wl_fields_options', $option);
                        }
                    }
                }
                $this->redirect("admin/wl_forms/".$form->name.'/'.$data['name']);
            }
        }
    }

    public function edit_form()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        $data = array();
        $formId = $_POST['formId'];

        $data['sidebar'] = ($this->data->post('sidebar')) ? $this->data->post('sidebar') : 0;
        $data['name'] = $this->data->post('name');
        $this->data->post('table') ? $data['table'] = $this->data->post('table') : '' ;
        $data['captcha'] = ($this->data->post('captcha')) ? $this->data->post('captcha') : 0;
        $data['title'] = $this->data->post('title');
        $data['type'] = $this->data->post('type') == 'get' ? 1 : 2;
        $data['type_data'] = 2;
        $data['send_mail'] = ($this->data->post('send_mail')) ? $this->data->post('send_mail') : 0;
        $data['success'] = $this->data->post('after');

        switch ($data['success'])
        {
            case '2':
            case '4':
                $data['success_data'] = $_SESSION['all_languages'] ? json_encode($_POST['lang']) :  $this->data->post('lang') ;
                break;

            case '3':
                $data['success_data'] = $this->data->post('afterValue');
                break;
            
            default:
                $data['success_data'] = '';
                break;
        }


        $data['send_sms'] = ($this->data->post('send_sms')) ? $this->data->post('send_sms') : 0;
        $data['sms_text'] = isset($_POST['sms_text']) ? $this->data->post('sms_text') : '';

        if($this->data->post('create'))
        {
            $fields = $this->db->getAllDataByFieldInArray('wl_fields', $formId, 'form');

            if($fields)
            {
                $fieldsName = array_map(function($f) { return '`'.$f->name.'` text'; }, $fields);
                $fieldsName = implode(', ', $fieldsName);

                $sql = "CREATE TABLE IF NOT EXISTS `{$data['table']}` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `date_add` int(11) NOT NULL,
                          {$fieldsName},
                          `language` text,
                          `new` tinyint(1),
                          `user_id` int(11) NOT NULL DEFAULT 0,
                          PRIMARY KEY (`id`),
                          KEY `new` (`new`)
                        ) ENGINE=InnoDB CHARSET=utf8;";
                $this->db->executeQuery($sql);
            }
            else $_SESSION['notify']->errors = "Спочатку додайте поля";
        }

        if(isset($data['type'],$data['type_data'],$formId))
        {
            $this->db->updateRow('wl_forms', $data, $formId);

            $this->db->executeQuery("DELETE FROM `wl_mail_active` WHERE `form` = $formId");
            if(isset($_POST['templates']) && $data['send_mail'])
            {
                foreach ($_POST['templates'] as $template) 
                {
                    $this->db->insertRow('wl_mail_active', array('form' => $formId, 'template' => $template, 'active' => 1));
                }
            }
        }

        $this->redirect("admin/wl_forms/".$data['name']);
    }

    public function info()
    {
        if($name = $this->data->uri(3))
        {
            if($this->userCan('form_'.$name)) {
                if($form = $this->db->getQuery("SELECT * FROM `wl_forms` WHERE `name` = '{$name}'"))
                {
                    $_SESSION['alias']->name = $form->title;
                    $formInfo = $this->db->select('wl_fields as f', '*', $form->id, 'form')
                                            ->join('wl_input_types', 'name as type_name', '#f.input_type')
                                            ->get('array');

                    $this->db->executeQuery("UPDATE `{$form->table}` SET `new` = 0 WHERE `new` = 1");
                    $tableInfo = $this->db->select($form->table.' as f')
                                            ->join('wl_users', 'name as user_name', '#f.user_id')
                                            ->order('id DESC')
                                            ->get('array');

                    $this->load->admin_view('wl_forms/info_view', compact('form', 'formInfo', 'tableInfo'));
                }
            }
            else
                $this->page_403();
        }

        $this->load->page_404(false);
    }

    public function export()
    {
        if($name = $this->data->uri(3))
        {
            if($this->userCan('form_'.$name)) {
                if($form = $this->db->getAllDataById('wl_forms', $name, 'name'))
                {
                    $min = $this->db->getQuery("SELECT `date_add` FROM `{$form->table}` ORDER BY `id` ASC LIMIT 1");
                    if($min)
                    {
                        $_SESSION['alias']->name = 'Експорт ' . $form->title;
                        $formInfo = $this->db->getQuery("SELECT name, title FROM `wl_fields` WHERE `form` = '{$form->id}'", 'array');

                        $this->load->admin_view('wl_forms/export_view', compact('form', 'formInfo', 'min'));
                    }
                    else
                    {
                        $_SESSION['notify'] = new stdClass();
                        $_SESSION['notify']->errors = 'Заявки відсутні';
                        $this->redirect();
                    }
                }
            }
            else
                $this->page_403();
        }

        $this->load->page_404(false);
    }

    public function export_xlsx()
    {
        if (empty($_POST) || empty($_POST['fields']))
            $this->redirect();
        
        if($form_id = $this->data->post('form_id'))
            if($form = $this->db->getAllDataById('wl_forms', $form_id))
        {
            $where = [];
            if(empty($_POST['all']))
            {
                $date_from = strtotime($this->data->post('date_from'));
                if(is_int($date_from) && $date_from > 0)
                    $where['date_add'] = '>'.$date_from;
                $date_to = strtotime($this->data->post('date_to'));
                if(is_int($date_to) && $date_to > 0)
                {
                    if(isset($where['date_add']))
                    {
                        if($date_to > $date_from)
                            $where['+date_add'] = '<='.$date_to;
                    }
                    else
                        $where['date_add'] = '<='.$date_to;
                }
            }
            $order = $this->data->post('order') == 'desc' ? 'id DESC' : '';
            
            if(empty($where))
                $data = $this->db->getAllData($form->table, $order);
            else
                $data = $this->db->getAllDataByFieldInArray($form->table, $where, $order);

            if ($data)
            {
                $a = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
                $goodfields = array('id' => 'ID', 'date_add' => "Додано");
                if ($fields = $this->db->getAllDataByFieldInArray('wl_fields', $form->id, 'form'))
                    foreach ($fields as $field)
                    {
                        $goodfields[$field->name] = htmlspecialchars_decode($field->title, ENT_QUOTES);
                    }
                // echo "<pre>";
                // print_r($goodfields);
                // print_r($data[0]);
                // echo "</pre>";
                // exit;
                $this->load->library('PHPExcel');

                // Set document properties
                $this->phpexcel->getProperties()->setCreator(SITE_NAME)
                    ->setLastModifiedBy(SITE_NAME)
                    ->setTitle("{$form->title} from " . SITE_NAME);

                $this->phpexcel->setActiveSheetIndex(0);
                $this->phpexcel->getActiveSheet()->setTitle($form->title);

                $x = 0;
                foreach ($_POST['fields'] as $field)
                {
                    if (array_key_exists($field, $goodfields))
                    {
                        $y = 1;
                        $xy = $a[$x] . $y++;
                        $this->phpexcel->getActiveSheet()->setCellValue($xy, $goodfields[$field]);
                        foreach ($data as $row)
                        {
                            $xy = $a[$x] . $y++;
                            if (!isset($data[0]->$field))
                                continue;
                            if ($field == 'date_add' || preg_match('/date/', $field))
                                $row->$field = date('d.m.Y H:i', $row->$field);
                            $this->phpexcel->getActiveSheet()->setCellValue($xy, $row->$field);
                        }
                        $x++;
                    }
                }

                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $this->phpexcel->setActiveSheetIndex(0);

                header('Cache-Control: max-age=0');
                // If you're serving to IE over SSL, then the following may be needed
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
                header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header('Pragma: public'); // HTTP/1.0

                $date = date('dmY');
                if ($_POST['file'] == 'xlsx')
                {
                    // Redirect output to a client’s web browser (Excel2007)
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . SITE_NAME . '-' . $form->name . '-' . $date . '.xlsx"');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
                    $objWriter->save('php://output');
                }
                elseif ($_POST['file'] == 'xls')
                {
                    // Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . SITE_NAME . '-' . $form->name . '-' . $date . '.xls"');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
                    $objWriter->save('php://output');
                }
                elseif ($_POST['file'] == 'csv')
                {
                    // Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . SITE_NAME . '-' . $form->name . '-' . $date . '.csv"');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'CSV')->setDelimiter(',')
                        ->setEnclosure('"')
                        ->setSheetIndex(0);
                    $objWriter->save('php://output');
                }
            }
            else
            {
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->errors = 'Заявки відсутні';
                $this->redirect();
            }
        }
    }

    public function createMailTemplate()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        $formId = $this->data->uri(3);
        if(isset($formId) && is_numeric($formId))
        {
            if($form = $this->db->getAllDataById('wl_forms', $formId))
            {
                $data = array();
                $data['from'] = SITE_EMAIL;
                $data['to'] = SITE_EMAIL;
                $data['multilanguage'] = $_SESSION['language'] != false ? 1 : 0;
                $data['savetohistory'] = 0;

                $text = "<h1>{$form->title}</h1>\n\n";
                $fields = $this->db->getAllDataByFieldInArray('wl_fields', $formId, 'form');
                if($fields)
                    foreach ($fields as $field) {
                        if($field->name == 'email')
                            $data['from'] = 'email';
                        $text .= "<p>{$field->title}: {{$field->name}}</p>\n";
                    }

                $mailTemplateId = $this->db->insertRow('wl_mail_templates', $data);

                $template = ['template' => $mailTemplateId, 'title' => $form->title, 'text' => $text];
                if($_SESSION['language'])
                {
                    foreach ($_SESSION['all_languages'] as $language) {
                        $template['language'] = $language;
                        $this->db->insertRow('wl_mail_templats_data', $template);
                    }
                }
                else
                    $this->db->insertRow('wl_mail_templats_data', $template);

                $this->db->insertRow('wl_mail_active', array('form' => $formId, 'template' => $mailTemplateId, 'active' => 1));
                $this->db->updateRow('wl_forms', array('send_mail' => 1), $formId);
                header('Location:'.SITE_URL.'admin/wl_mail_template/'.$mailTemplateId);
            }
            else
                $this->page_404(false);
        }
        else
            $this->redirect();
    }

    public function deleteForm()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        $form = $this->data->post('form');
        $deleteTable = $this->data->post('deleteTable');
        $tableName = $this->data->post('tableName');

        $this->db->deleteRow('wl_forms', $form);
        $this->db->deleteRow('wl_fields', $form, 'form');

        if($deleteTable){
            $this->db->executeQuery("DROP TABLE {$tableName}");
        }
    }

    public function deleteField()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();

        $field = $this->data->post('field');
        $fieldName = $this->data->post('fieldName');
        $tableName = $this->data->post('tableName');

        $this->db->deleteRow('wl_fields', $field);

        if($this->db->getQuery("SHOW TABLES LIKE '{$tableName}'"))
            $this->db->executeQuery("ALTER TABLE `{$tableName}` DROP `{$fieldName}`");
    }

    public function deleteRow()
    {
        if(!$_SESSION['user']->admin)
            $this->page_403();
        
        $res = array('result' => false, 'error' => 'Access denied! Only admin');
        if($_SESSION['user']->type == 1 && !empty($_POST['table']) && !empty($_POST['id']) && is_numeric($_POST['id'])) {
            if($table = $this->data->post('table'))
            {
                $t = explode('_', $table);
                if(count($t) > 1)
                {
                    if(in_array($t[0], array('s', 'wl')))
                    {
                        $res['error'] = 'Access denied! Only non system tables';
                        $this->load->json($res);
                    }
                }
                $this->db->deleteRow($table, $_POST['id']);
                $res = array('result' => true);
            }
        }
        $this->load->json($res);
    }

    public function page_403()
    {
        $_SESSION['alias'] = new stdClass();
        $_SESSION['alias']->id = -1;
        $_SESSION['alias']->name = '403 Forbidden';
        $_SESSION['alias']->alias = 'admin403';
        $_SESSION['alias']->service = false;
        $_SESSION['alias']->table = $_SESSION['alias']->text = '';
        $_SESSION['alias']->js_load = $_SESSION['alias']->js_init = $_SESSION['alias']->breadcrumb = array();

        if($options = $this->db->getAllDataByFieldInArray('wl_options', array('service' => 0, 'alias' => 0)))
            foreach($options as $opt) {
                $key = $opt->name;
                @$_SESSION['option']->$key = $opt->value;
            }
        header('HTTP/1.0 403 Forbidden');
        $this->load->admin_view('403_view');
        exit;
    }
}

?>