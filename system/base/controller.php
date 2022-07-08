<?php  if (!defined('SYS_PATH')) exit('Access denied');

/*
 * Шлях: SYS_PATH/base/controller.php
 *
 * Всі контроллери успадковують цей клас
 */

class Controller extends Loader {
	
	public $load;
    private $wl_aliases_cooperation = array();
	
	/**
	 * Визиваємо батьківський конструктор та копіюємо ідентифікатор на обєкт
         * це потрібно для надання логіки. Відтак для завантаження скажімо бібліотеки
         * ми не пишемо $this->library(library_name), а пишемо $this->load->library(library_name)
	 */
	function __construct($init_page = false)
    {
        parent::__construct();
        $this->load = $this;
        
        if($init_page)
        {
            $actions = $this->db->cache_get('__page_before_init', 'wl_aliases');
            if($actions === NULL)
            {
                $actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => 0, 'type' => '__page_before_init'));
                $this->db->cache_add('__page_before_init', $actions, 'wl_aliases');
            }
            if($actions)
                foreach ($actions as $action) {
                    if(empty($_SESSION['__page_before_init'][$action->alias2]) || $_SESSION['__page_before_init'][$action->alias2] < time())
                    {
                        $_SESSION['__page_before_init'][$action->alias2] = time() + 15 * 60; //15min
                        // для того щоб відключити кешування у сесії на 15 хв
                        // $_SESSION['__page_before_init'][$action->alias2] = 0;
                        $this->load->function_in_alias($action->alias2, '__page_before_init');
                    }
                }
        }

        if(!empty($_SESSION['alias']->id))
        {
            $cooperation = $this->db->cache_get('wl_aliases_cooperation');
            if($cooperation === NULL)
            {
                if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1'))
                    foreach ($cooperation as $c) {
                        if(!isset($this->wl_aliases_cooperation[$c->type]))
                            $this->wl_aliases_cooperation[$c->type] = [$c->alias2];
                        else
                            $this->wl_aliases_cooperation[$c->type][] = $c->alias2;
                    }
                $this->db->cache_add('wl_aliases_cooperation', $this->wl_aliases_cooperation);
            }
            else
                $this->wl_aliases_cooperation = $cooperation;
        }
	}
	
	/**
	 * Викликаємо батьківський метод з ідентифікатором на обєкт
	 *
	 * @params $class ім'я класу
	 * @params $var завжди не задана(null)
	 */
	public function library($classname, $var = null)
    {
		parent::library($classname, $this);
	}

    public function userIs()
    {
    	if(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0)
    		return true;
    	return false;
    }

    public function userCan($permissions = '')
    {
    	if(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0)
        {
    		if($_SESSION['user']->admin)
                return true;
    		else
            {
    			if($permissions == '')
                    $permissions = $_SESSION['alias']->alias;
    			if($_SESSION['user']->manager == 1 && in_array($permissions, $_SESSION['user']->permissions))
                    return true;
    		}
    	}
    	return false;
    }

    public function get__wl_cooperation($function_name = false)
    {
        if(empty($function_name))
            return $this->wl_aliases_cooperation;

        if(isset($this->wl_aliases_cooperation[$function_name]))
            return $this->wl_aliases_cooperation[$function_name];

        return false;
    }

    public function init__wl_cooperation($function_name = false, $data = NULL)
    {
        if(empty($function_name))
            return NULL;

        if(isset($this->wl_aliases_cooperation[$function_name]))
        {
            foreach ($this->wl_aliases_cooperation[$function_name] as $aliasIdInit) {
                $data = $this->function_in_alias($aliasIdInit, $function_name, $data);
            }
            return $data;
        }

        return false;
    }
	
}

?>