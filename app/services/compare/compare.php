<?php

class compare extends Controller {
				
    function _remap($method, $data = array())
    {
        if (method_exists($this, $method))
        {
            if(empty($data))
                $data = null;
            return $this->$method($data);
        }
        else
            $this->index($method);
    }

    public function index($group = false)
    {
        if($group && !is_numeric($group))
            $this->load->page_404();
        $this->wl_alias_model->setContent(0, 201);
        $this->load->smodel('compare_model');
        $groups = false;
        if($_SESSION['option']->groupBy == 'alias')
            $group = 0;
        else
        {
            $groups = $this->compare_model->getGroupsWithData();
            if(!empty($groups) && count($groups) == 1)
                $group = $groups[0]->id;
        }
        if(is_numeric($group))
        {
            $_SESSION['alias']->content = $group;
            $where = array('user' => $this->compare_model->getUserId(), 'status' => 1);
            if($group)
                $where['group'] = $group;
            $compares = $this->compare_model->getLikesWithData($where);
            $this->load->page_view('compare_view', array('compares' => $compares));
        }
        else
            $this->load->page_view('index_view', array('groups' => $groups));
    }

    public function getItems()
    {
        $this->load->smodel('compare_model');
        $where = array('user' => $this->compare_model->getUserId(), 'status' => 1);
        $this->load->json($this->compare_model->getItems($where));
    }

    public function getGroups()
    {
        if($_SESSION['option']->groupBy != 'alias')
        {
            $this->load->smodel('compare_model');
            if($groups = $this->compare_model->getGroupsWithData())
                if(count($groups) > 1)
                {
                    if(!empty($_POST['html']))
                    {
                        $links = '';
                        foreach ($groups as $group) {
                            $links .= '<a href="'.SITE_URL.$_SESSION['alias']->alias.'/'.$group->id.'">'.$group->name.'</a>';
                        }
                    }
                    else
                    {
                        $links = [];
                        foreach ($groups as $group) {
                            $links[$group->name] = $_SESSION['alias']->alias.'/'.$group->id;
                        }
                    }
                    $this->load->json($links);
                }
        }
        $this->load->json(false);
    }

    public function add()
    {
        if($this->userIs())
        {
            if (isset($_POST['alias']) && isset($_POST['content']) && is_numeric($_POST['alias']) && is_numeric($_POST['content']) && $_POST['alias'] > 0)
            {
                $compareGroup = 0;
                if($_SESSION['option']->groupBy != 'alias')
                    $compareGroup = $this->getGroupByAliasContent($_POST['alias'], $_POST['content']);
                if(is_numeric($compareGroup))
                {
                    $this->load->smodel('compare_model');
                    $this->load->json($this->compare_model->add($compareGroup));
                }
            }
            else
                $this->load->json('Compare error: no set page alias or content');
        }
        else
            $this->load->json('no login');
    }

    public function cancel()
    {
        if($this->userIs())
        {
            if (isset($_POST['id']) && is_numeric($_POST['id']))
            {
                $this->load->smodel('compare_model');
                $this->load->json($this->compare_model->cancel());
            }
            else
                $this->load->json('Compare error: no set page alias or content');
        }
        else
            $this->load->json('no login');
    }

    public function __get_Search($content='')
    {
        return false;
    }

    private function getGroupByAliasContent($alias, $content)
    {
        if($_POST['content'] > 0)
        {
            if($product = $this->load->function_in_alias($alias, '__get_Product', $content))
            {
                if(!empty($product->parents))
                {
                    if($_SESSION['option']->groupBy == 'grandParent')
                        return $product->parents[0]->id;
                    if($_SESSION['option']->groupBy == 'parent')
                        return $product->group;
                }
                return 0;
            }
        }
        else if(FALSE && $content < 0)
        {
            if($group = $this->load->function_in_alias($alias, '__get_Group', -$content))
            {
                if(!empty($group->parents))
                {
                    if($_SESSION['option']->groupBy == 'grandParent')
                        return $group->parents[0]->id;
                    if($_SESSION['option']->groupBy == 'parent')
                        return $group->parent;
                }
                return 0;
            }
        }
        return false;
    }
	
}

?>