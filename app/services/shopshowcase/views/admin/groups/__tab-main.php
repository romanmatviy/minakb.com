<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_group" method="POST" enctype="multipart/form-data" class="form-horizontal">
	<input type="hidden" name="id" value="<?=$group->id?>">
	<div class="form-group">
        <label class="col-md-3 control-label">id на сайті</label>
        <div class="col-md-9">
            <strong><?=$group->id?></strong>
        </div>
    </div>
	<div class="form-group">
        <label class="col-md-3 control-label">Батьківська група</label>
        <div class="col-md-9">
			<select name="parent" class="form-control" required>
				<option value="0">Немає</option>
				<?php if(!empty($groups)){
					$list = array();
					$emptyParentsList = array();
					foreach ($groups as $g) {
						$list[$g->id] = $g;
						$list[$g->id]->child = array();
						if(isset($emptyParentsList[$g->id])){
							foreach ($emptyParentsList[$g->id] as $c) {
								$list[$g->id]->child[] = $c;
							}
						}
						if($g->parent > 0) {
							if(isset($list[$g->parent]->child)) $list[$g->parent]->child[] = $g->id;
							else {
								if(isset($emptyParentsList[$g->parent])) $emptyParentsList[$g->parent][] = $g->id;
								else $emptyParentsList[$g->parent] = array($g->id);
							}
						}
					}
					if(!empty($list)){
						function showList($group_id, $group_parent, $all, $list, $parent = 0, $level = 0)
						{
							$prefix = '';
							for ($i=0; $i < $level; $i++) { 
								$prefix .= '- ';
							}
							foreach ($list as $g) if($g->parent == $parent) {
								if($group_id == $g->id){
									echo('<optgroup label="'.$prefix.$g->name.'"></optgroup>');
								} else {
									$selected = '';
									if($g->id == $group_parent) $selected = 'selected';
									echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
								}
								if(!empty($g->child)) {
									$l = $level + 1;
									$childs = array();
									foreach ($g->child as $c) {
										$childs[] = $all[$c];
									}
									showList ($group_id, $group_parent, $all, $childs, $g->id, $l);
								}
							}
							return true;
						}
						showList($group->id, $group->parent, $list, $list);
					}
				} ?>
			</select>
		</div>
	</div>
	<div class="form-group">
        <label class="col-md-3 control-label">Власна адреса</label>
        <div class="col-md-9">
            <input type="text" name="alias" value="<?=$group->alias?>" class="form-control" required>
        </div>
    </div>

	<?php
	if (isset($_SESSION['admin_options']['groups:additional_fields']) && $_SESSION['admin_options']['groups:additional_fields'] != '') {
		$fields = explode(',', $_SESSION['admin_options']['groups:additional_fields']);
		foreach ($fields as $field) {
	?>
		<div class="form-group">
	        <label class="col-md-3 control-label"><?=$field?></label>
	        <div class="col-md-9">
	            <input type="text" name="<?=$field?>" value="<?=$group->$field?>" class="form-control">
	        </div>
	    </div>
	<?php
		}
	}
	?>
	<div class="form-group">
        <label class="col-md-3 control-label">Стан активності</label>
        <div class="col-md-9">
				<input type="radio" name="active" value="1" <?=($group->active == 1)?'checked':''?> id="active-1"><label for="active-1">Так</label>
				<input type="radio" name="active" value="0" <?=($group->active == 0)?'checked':''?> id="active-0"><label for="active-0">Ні</label>
		</div>
	</div>
	<div class="form-group">
        <label class="col-md-3 control-label">Сховати групу <br> (група активна, не виводиться у каталозі)</label>
        <div class="col-md-9">
				<input type="radio" name="hide" value="1" <?=($group->hide == 1)?'checked':''?> id="hide-1"><label for="hide-1">Так</label>
				<input type="radio" name="hide" value="0" <?=($group->hide == 0)?'checked':''?> id="hide-0"><label for="hide-0">Ні</label>
		</div>
	</div>
	<div class="form-group">
        <label class="col-md-3 control-label"></label>
        <div class="col-md-9">
            <button type="submit" class="btn btn-sm btn-success">Зберегти</button>
        </div>
    </div>
</form>