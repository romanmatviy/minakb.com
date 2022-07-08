<?php require_once '@commons/_widgets.php';

$__dashboard_subview = array();
if($actions = $this->db->select('wl_aliases_cooperation', 'alias1, alias2', array('alias1' => '<0', 'type' => '__dashboard_subview'))
                        ->join('wl_aliases', 'alias', '#alias2')
                        ->order('alias1')
                        ->get('array'))
    foreach ($actions as $action) {
        if($this->userCan($action->alias))
            if($subview = $this->load->function_in_alias($action->alias2, '__dashboard_subview', true, true))
                $__dashboard_subview[-$action->alias1] = $subview;
    }

if(!empty($__dashboard_subview))
{
    $show_6 = false;
    foreach ($__dashboard_subview as $key => $subview) {
        if($key < 10) {
            echo '<div class="row">
                <div class="col-md-12 ui-sortable" data-sortable-id="__dashboard-'.$key.'">'.
                    $subview .
                '</div>
            </div>';
        }
        else if($key >= 10 && $key < 20)
        {
            if(!$show_6)
            {
                echo '<div class="row">';
                $show_6 = true;
            }
            echo '<div class="col-md-6 ui-sortable" data-sortable-id="__dashboard-'.$key.'">'.
                    $subview .
                '</div>';
        }
    }
    if($show_6)
        echo "</div>";
} ?>
<div class="row">
    <div class="col-md-8 ui-sortable">
    	<?php require_once '@commons/_wl_statistic.php'; ?>
    </div>
    <?php if(!empty($__dashboard_subview))
    {
        foreach ($__dashboard_subview as $key => $subview) {
            if($key >= 20) {
                echo '<div class="col-md-4 ui-sortable" data-sortable-id="__dashboard-'.$key.'">'.
                        $subview .
                    '</div>';
            }
        }
    } ?>
    <div class="col-md-4 ui-sortable" data-sortable-id="__dashboard-users">
		<?php require_once '@commons/_wl_users.php'; ?>
    </div>
</div>