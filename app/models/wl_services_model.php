<?php

class wl_services_model {

	public function getServicesList()
	{
		return $this->db->getAllData('wl_services');
	}

	public function loadService($alias)
	{
		if(!empty($alias->service_name))
		{
			if(isset($_SESSION['alias-cache'][$alias->id]->options))
				$_SESSION['option'] = $_SESSION['alias-cache'][$alias->id]->options;
			else
			{
				if($options = $this->db->getAllDataByFieldInArray('wl_options', array('service' => array(0, $alias->service), 'alias' => array(0, $alias->id)), 'service, alias'))
					foreach($options as $opt){
						$key = $opt->name;
						@$_SESSION['option']->$key = $opt->value;
					}
				if(!empty($_SESSION['option']))
					$_SESSION['alias-cache'][$alias->id]->options = clone $_SESSION['option'];
				else
					$_SESSION['alias-cache'][$alias->id]->options = null;
			}

			$_SESSION['alias']->service = $alias->service_name;
			$_SESSION['service'] = new stdClass();
			$_SESSION['service']->id = $alias->service;
			$_SESSION['service']->name = $alias->service_name;
			$_SESSION['service']->table = $alias->service_table;

			return true;
		}
		return false;
	}
	
}

?>