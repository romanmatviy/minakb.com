<?php

class options_model {

	public function table($sufix = '_options', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function getOptions($group = 0, $active = true)
	{
		$where = ''; $where_gn = ''; $select_gn = '';
		if($active) $where = 'AND o.active = 1';
		else {
			$select_gn = ', g.name as group_name';
			$where_gn = "LEFT JOIN `wl_ntkd` as g ON g.content = '-{$group}' AND g.alias = '{$_SESSION['alias']->id}'";
			if($_SESSION['language']) $where_gn .= " AND g.language = '{$_SESSION['language']}'";
		}
		$this->db->executeQuery("SELECT o.*, t.name as type_name {$select_gn} FROM `{$this->table('_options')}` as o LEFT JOIN wl_input_types as t ON t.id = o.type {$where_gn} WHERE o.wl_alias = '{$_SESSION['alias']->id}' AND o.group = '{$group}' {$where} ORDER BY o.position ASC");
        if($this->db->numRows() > 0){
            $options = $this->db->getRows('array');

			$where = '';
            if($_SESSION['language']) $where = "AND `language` = '{$_SESSION['language']}'";
            foreach ($options as $option) {
            	$this->db->executeQuery("SELECT * FROM `{$this->table('_options_name')}` WHERE `option` = '{$option->id}' {$where}");
            	if($this->db->numRows() == 1){
            		$ns = $this->db->getRows();
            		$option->name = $ns->name;
            		$option->sufix = $ns->sufix;
            	}
            }

			return $options;
		}
		return null;
	}

	public function add_option($property = false){
		$data = array();
		$data['wl_alias'] = $_SESSION['alias']->id;
		if(isset($_POST['group']) && is_numeric($_POST['group'])) $data['group'] = $_POST['group'];
		if($property && isset($_POST['id'])) $data['group'] = -1 * $_POST['id'];
		if(isset($_POST['type']) && is_numeric($_POST['type'])) $data['type'] = $_POST['type'];
		$data['active'] = 1;
		$data['filter'] = 0;
		if($this->db->insertRow($this->table('_options'), $data)){
			$id = $this->db->getLastInsertedId();
			$data = array();
			$data['alias'] = '';

			$ntkd = array();
			$ntkd['option'] = $id;
			if($_SESSION['language']){
				foreach ($_SESSION['all_languages'] as $lang) {
					$ntkd['language'] = $lang;
					$ntkd['name'] = $_POST['name_'.$lang];
					$ntkd['sufix'] = $_POST['sufix_'.$lang];
					if($lang == $_SESSION['language']){
						$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
					}
					$this->db->insertRow($this->table('_options_name'), $ntkd);
				}
			} else {
				$ntkd['name'] = $_POST['name'];
				$ntkd['sufix'] = $_POST['sufix'];
				$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
				$this->db->insertRow($this->table('_options_name'), $ntkd);
			}
			$data['alias'] = $id .'-'. $data['alias'];
			
			$group = 0;
			if($_SESSION['option']->useGroups){			
				if(isset($_POST['group']) && is_numeric($_POST['group'])) $group = $_POST['group'];	
			}
			if($group == 0) $group = '>=0';
			$data['position'] = $this->db->getCount($this->table('_options'), array('wl_alias'=> $_SESSION['alias']->id, 'group' => $group));
			if($this->db->updateRow($this->table('_options'), $data, $id)) return $id;
		}
		return false;
	}

	public function saveOption($id)
	{
		$data = array('active' => 1, 'filter' => 0);
		if(isset($_POST['alias']) && $_POST['alias'] != '') $data['alias'] = $_POST['id'] . '-' . $_POST['alias'];
		if(isset($_POST['active']) && $_POST['active'] == 0) $data['active'] = 0;
		if(isset($_POST['filter']) && $_POST['filter'] == 1) $data['filter'] = 1;
		if(isset($_POST['type']) && is_numeric($_POST['type'])) $data['type'] = $_POST['type'];
		if($_SESSION['option']->useGroups){
			if(isset($_POST['group']) && is_numeric($_POST['group'])) $data['group'] = $_POST['group'];
		}
		if($this->db->updateRow($this->table('_options'), $data, $id)){
			if($_SESSION['language']){
				foreach ($_SESSION['all_languages'] as $lang){
					if(isset($_POST['name_'.$lang]) && isset($_POST['sufix_'.$lang])){
						$this->db->executeQuery("UPDATE `{$this->table('_options_name')}` SET `name` = '{$_POST['name_'.$lang]}', `sufix` = '{$_POST['sufix_'.$lang]}' WHERE `option` = '{$id}' AND `language` = '{$lang}'");
					}
				}
			} else {
				if(isset($_POST['name']) && isset($_POST['sufix'])){
					$data = array();
					$data['name'] = $_POST['name'];
					$data['sufix'] = $_POST['sufix'];
					$this->db->updateRow($this->table('_options_name'), $data, $id, 'option');
				}
			}
			if(isset($_POST['type']) && is_numeric($_POST['type'])){
				$type = $this->db->getAllDataById('wl_input_types', $_POST['type']);
				if($type->options == 1){
					$options = array();
					foreach ($_POST as $key => $value) {
						$key = explode('_', $key);
						if($key[0] == 'option' && isset($key[1]) && is_numeric($key[1]) && $key[1] > 0) $options[] = $key[1];
					}
					if($options){
						foreach ($options as $opt) {
							$this->db->updateRow($this->table('_options_name'), array('name' => $_POST['option_'.$opt]), $opt);
						}
					}
					if($_SESSION['language']){
						if(isset($_POST['option_0_'.$_SESSION['language']]) && is_array($_POST['option_0_'.$_SESSION['language']])){
							for($i = 0; $i < count($_POST['option_0_'.$_SESSION['language']]); $i++){
								$data = array();
								$data['wl_alias'] = $_SESSION['alias']->id;
								$data['group'] = $id * -1;
								$data['type'] = 0;
								$data['position'] = 0;
								$data['active'] = 1;
								$this->db->insertRow($this->table('_options'), $data);
								$option_id = $this->db->getLastInsertedId();
								foreach ($_SESSION['all_languages'] as $lang){
									$data = array();
									$data['option'] = $option_id;
									$data['language'] = $lang;
									$data['name'] = $_POST['option_0_'.$lang][$i];
									$this->db->insertRow($this->table('_options_name'), $data);
								}
							}
						}
					} else {
						if(isset($_POST['option_0']) && is_array($_POST['option_0'])){
							foreach ($_POST['option_0'] as $option) {
								$data = array();
								$data['wl_alias'] = $_SESSION['alias']->id;
								$data['group'] = $id * -1;
								$data['type'] = 0;
								$data['position'] = 0;
								$data['active'] = 1;
								$this->db->insertRow($this->table('_options'), $data);
								$option_id = $this->db->getLastInsertedId();
								$data = array();
								$data['option'] = $option_id;
								$data['name'] = $option;
								$this->db->insertRow($this->table('_options_name'), $data);
							}
						}
					}
				}
			}
			return true;
		}
		return false;
	}

	public function deleteOption($id)
	{
		$option = $this->db->getAllDataById($this->table('_options'), $id);
		if($option){
			$this->db->deleteRow($this->table('_product_options'), $option->id, 'option');
			$this->db->deleteRow($this->table('_options_name'), $option->id, 'option');
			$id = $option->id * -1;
			$options = $this->db->getAllDataByFieldInArray($this->table('_options'), $id, 'group');
			if($options){
				foreach ($options as $opt) {
					$this->db->deleteRow($this->table('_options_name'), $opt->id, 'option');
				}
			}
			$this->db->deleteRow($this->table('_options'), $option->id);
			$this->db->deleteRow($this->table('_options'), $id, 'group');
			$this->db->executeQuery("UPDATE `{$this->table('_options')}` SET `position` = position - 1 WHERE `position` > '{$option->position}' AND `group` = '{$option->group}'");
			return true;
		}
		return false;
	}
	
}

?>