<?php 

/**
* Універсальна модель для зміни порядку елменту в таблиці.
*/
class wl_position_model 
{

	public $where = '';
	public $table = '';
	
	function change($id, $new_pos){
		if($this->where != '') $this->where = 'WHERE '.$this->where;
		$table = $this->table;
		if($table != ''){
			$this->db->executeQuery("SELECT id, position as pos FROM `{$table}` {$this->where} ORDER BY `position` ASC ");
			if($this->db->numRows() > 0){
	            $products = $this->db->getRows();
				$old_pos = 0;
				foreach($products as $a) if($a->id == $id) { $old_pos = $a->pos; break; }
				if($new_pos < $old_pos)	foreach($products as $a){
					if($a->pos >= $new_pos){
						if($a->pos != $old_pos && $a->pos < $old_pos){
							$pos = $a->pos + 1;
							$this->db->executeQuery("UPDATE `{$table}` SET `position` = '{$pos}' WHERE `id` = {$a->id}");
						}
						if($a->pos == $old_pos){ 
							$this->db->executeQuery("UPDATE `{$table}` SET `position` = '{$new_pos}' WHERE `id` = {$a->id}");
							return true;
						}
					}
				}
				if($new_pos > $old_pos)	foreach($products as $a){
					if($a->pos <= $new_pos){
						if($a->pos != $old_pos && $a->pos > $old_pos){
							$pos = $a->pos - 1;
							$this->db->executeQuery("UPDATE `{$table}` SET `position` = '{$pos}' WHERE `id` = {$a->id}");
						}
						if($a->pos == $old_pos){ 
							$this->db->executeQuery("UPDATE `{$table}` SET `position` = '{$new_pos}' WHERE `id` = {$a->id}");
						}
					} else return true;
				}
			}
			return true;
		}
		return false;
	}

}

 ?>