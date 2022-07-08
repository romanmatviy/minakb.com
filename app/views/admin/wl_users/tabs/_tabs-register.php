<?php

$this->db->executeQuery("SELECT r.*, d.title, d.help_additionall as help FROM wl_user_register as r LEFT JOIN wl_user_register_do as d ON d.id = r.do WHERE r.user = {$user->id} ORDER BY r.id DESC");
if($this->db->numRows() > 0){
	$register = $this->db->getRows('array');
?>
<!-- begin col-6 -->
<div class="col-md-12">
    <!-- begin panel -->
    <div class="panel panel-inverse" data-sortable-id="form-stuff-2">
        <div class="panel-heading">
            <h4 class="panel-title">Реєстр дій</h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered ">
            <thead>
				<tr>
					<th>id</th>
					<th>Дата</th>
					<th>Дія</th>
					<th>Додатково</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($register as $r) { ?>
				<tr>
					<td><?=$r->id?></td>
					<td><?=date("d.m.Y H:i", $r->date)?></td>
					<td><?=$r->title?></td>
					<td title="<?=$r->help?>"><?=$r->additionally?></td>			
				</tr>
			<?php } ?>
			</tbody>
		</table>
        </div>
    </div>
    <!-- end panel -->
</div>
<!-- end col-6 -->
<?php } ?>