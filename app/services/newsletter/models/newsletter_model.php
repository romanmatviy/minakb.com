<?php

class newsletter_model {

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function get_templates()
	{
		return $this->db->getAllData($this->table('_templates'));
	}

    public function get_template($id)
    {
    	if($template = $this->db->getAllDataById($_SESSION['service']->table.'_templates', $id))
    	{
    		$template->files = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_files', $template->id, 'template');
    		$template->to_user_types = unserialize($template->to_user_types);
        	if(!is_array($template->to_user_types))
        		$template->to_user_types = [];
        	return $template;
    	}
    	return false;
    }

    public $all_users = false;
    public function getListActiveMails($user_types, $start = -1)
    {
        if($this->all_users)
        	$this->db->select('wl_users', 'id, email, name, type, registered, auth_id', [ 'type' => $user_types, 'status' => '!3']);
        else
            $this->db->select('wl_users', 'id, email, name, type, registered, auth_id', [ $_SESSION['service']->table => 1, 'type' => $user_types, 'status' => '!3']);
        if($start >= 0)
            $this->db->limit($start, $_SESSION['option']->sent_per_part);
        return $this->db->get('array');
    }
	
}

?>
