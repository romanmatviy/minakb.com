<?php  if (!defined('SYS_PATH')) exit('Access denied');

/*
 * Шлях: SYS_PATH/libraries/form.php
 *
 * Отримуємо POST дані і частини URI
 *
 * Версія 0 (19.10.2015) Бібліотека не тестувалася!
 */

class Form extends Controller {

	public $errors = array();

	public function insertFromForm($form='', $additionally = array(), $user = -1)
    {
        if($form != ''){
        	$this->library('db');
            $form = $this->db->getAllDataById('wl_forms', $form, 'name');
            if($form && $form->table != '' && $form->type > 0 && $form->type_data > 0){
                $fields = $this->db->getQuery("SELECT f.*, t.name as type_name FROM wl_fields as f LEFT JOIN wl_input_types as t ON t.id = f.input_type WHERE f.form = {$form->id}", 'array');
                if($fields){
                	$data = array();
                    $data_id = array();
                	$this->errors = array();
                	foreach ($fields as $field) {
                		$input_data = null;
                		if($form->type == 1) $input_data = $this->get($field->name);
                		elseif($form->type == 2) $input_data = $this->post($field->name);
                		if($field->required && $input_data == null) {
                			$this->errors[] = "Field '{$field->title}' is required!";
                		}
                		if($input_data){
                            $data[$field->name] = $input_data;
                			$data_id[$field->name] = $field->id;
                		}
                	}
                	if(!empty($data) && empty($this->errors)){
                		if(!empty($additionally)) $data = array_merge ($data, $additionally);
                		if($form->type_data == 1){
                			foreach ($data as $field => $value) {
                				if($user == 0 && isset($_SESSION['user']->id) && $_SESSION['user']->id > 0){
                					$row['user'] = $_SESSION['user']->id;
                				} elseif($user > 0){
                					$row['user'] = $user;
                				}
                				$row['field'] = $data_id[$field];
                				$row['value'] = $value;
                				$this->db->insertRow($form->table, $row);
                			}
                		} elseif($form->type_data == 2){
                			$this->db->insertRow($form->table, $data);
                			$data['id'] = $this->db->getLastInsertedId();
                		}
                		return $data;
                	}
                }
            }
        }
        return false;
    }

    public function saveFromForm($form='', $where = array(), $additionally = array(), $user = 0)
    {
        if($form != ''){
        	$this->library('db');
            $form = $this->db->getAllDataById('wl_forms', $form, 'name');
            if($form && $form->table != '' && $form->type > 0 && $form->type_data > 0){
                $fields = $this->db->getQuery("SELECT f.*, t.name as type_name FROM wl_fields as f LEFT JOIN wl_input_types as t ON t.id = f.input_type WHERE f.form = {$form->id}", 'array');
                if($fields){
                	$data = array();
                    $fields_id = array();
                	$this->errors = array();
                	foreach ($fields as $field) {
                        $fields_id[$field->name] = $field->id;
                		$input_data = null;
                		if($form->type == 1) $input_data = $this->data->get($field->name);
                		elseif($form->type == 2) $input_data = $this->data->post($field->name);
                		if($field->required && $input_data == null) {
                			$this->errors[] = "Field '{$field->title}' is required!";
                		}
                		$data[$field->name] = $input_data;
                	}
                	if(!empty($data) && empty($this->errors)){
                		if(!empty($additionally)) $data = array_merge ($data, $additionally);
                		if($form->type_data == 1){
                            if($user == 0 && isset($_SESSION['user']->id) && $_SESSION['user']->id > 0){
                                $row['user'] = $_SESSION['user']->id;
                            } elseif($user > 0){
                                $row['user'] = $user;
                            }

                            if(!empty($where)) $row = array_merge ($row, $where);
                			foreach ($data as $field => $value) {
                				$row['field'] = $fields_id[$field];
                				$old = $this->db->getAllDataById($form->table, $row);
                				if($old){
                					if($old->value != $value){
                						$this->db->updateRow($form->table, array('value' => $value), $old->id);
                					}
                                    if($value == ''){
                                        $this->db->deleteRow($form->table, $old->id);
                                    }
                				} elseif($value != '') {
	                				$row['value'] = $value;
	                				$this->db->insertRow($form->table, $row);
                                    unset($row['value']);
                				}
                			}
                		} elseif($form->type_data == 2){
                            $data['user'] = $_SESSION['user']->id;

                            $this->db->deleteRow($form->table, $data['user'], 'user');
                			$this->db->insertRow($form->table, $data);
                			$data['id'] = $this->db->getLastInsertedId();
                		}
                		return $data;
                	}
                }
            }
        }
        return false;
    }

    public function showForm($form='', $data = array(), $action = '', $user = 0)
    {
    	if($form != ''){
        	$this->library('db');
            $form = $this->db->getAllDataById('wl_forms', $form, 'name');
            if($form && $form->table != '' && $form->type > 0 && $form->type_data > 0){
                $fields = $this->db->getQuery("SELECT f.*, t.name as type_name, t.options as type_options FROM wl_fields as f
                    LEFT JOIN wl_input_types as t ON t.id = f.input_type
                    LEFT JOIN wl_users as u ON u.id = '{$_SESSION['user']->id}'
                    WHERE f.form = {$form->id}", 'array');

           		if($form->type_data == 1){
           			foreach ($fields as $field) {
        				if($user == 0 && isset($_SESSION['user']->id) && $_SESSION['user']->id > 0){
        					$row['user'] = $_SESSION['user']->id;
        				} elseif($user > 0){
        					$row['user'] = $user;
        				}
        				$row['field'] = $field->id;
        				$value = $this->db->getAllDataById($form->table, $row);
                        if($value) $field->value = $value->value;
        				if(isset($data[$field->name])) $field->value = $data[$field->name];
        			}
           		} elseif($form->type_data == 2){
        			$row = $this->db->getAllDataById($form->table, $data);

        			if($row){
        				foreach ($fields as $field) {
        					$name = $field->name;
        					if(isset($row->$name))
        						$field->value = $row->$name;
        				}
        			}
        		}
                foreach ($fields as $field) {
                    if($field->type_options) $field->options = $this->db->getAllDataByFieldInArray('wl_fields_options', $field->id, 'field');
                }
           		include APP_PATH."views/@wl_forms/index_view.php";
            }
        }
        return false;
    }

}

?>
