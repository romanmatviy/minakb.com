<?php

	$services = $this->db->getAllData('wl_services');
	$services_name = array();

?>


<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Інстальовані сервіси:</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>id</th>
								<th>Група</th>
								<th>Назва</th>
								<th>Службова назва</th>
								<th>Службова таблиця</th>
								<th>Версія</th>
                            </tr>
                        </thead>
                        <tbody>
						<?php if($services) foreach ($services as $s) { $services_name[] = $s->name; ?>
							<tr>
								<td><a href="<?=SITE_URL?>admin/wl_services/<?=$s->name?>"><?=$s->id?></a></td>
								<td><?=$s->group?></td>
								<td title="<?=$s->description?>"><a href="<?=SITE_URL?>admin/wl_services/<?=$s->name?>"><?=$s->title?></a></td>
								<td><?=$s->name?></td>
								<td><?=$s->table?></td>
								<td><?=$s->version?></td>
							</tr>
						<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- end col-12 -->
</div>
<!-- end row -->

<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Неінстальовані сервіси:</h4>
            </div>
            <div class="panel-body">
				<form action="<?=SITE_URL?>admin/wl_services/install" method="POST">
				<?php 
					$services = '';
					$files = scandir(APP_PATH.'services');
					$files[0] = null;
					$files[1] = null;
					foreach ($files as $dir) {
						if(!in_array($dir, $services_name) && $dir){
							$description = APP_PATH.'services'.DIRSEP.$dir.DIRSEP.'description.txt';
							if(file_exists($description)) $description = file_get_contents($description); else $description = '';
							$services .= '<span id="wl-non-'.$dir.'" title="'.$description.'">'.$dir.' <button onclick="install(\''.$dir.'\')">Інсталювати</button><br></span>';
						}
					};
					if($services == '') echo('Сервіси відсутні. Скопіюйте у папку APP_PATH/services'); else echo($services);
				?>
					<input type="hidden" id="name" name="name" value="">
				</form>
            </div>
        </div>
    </div>
    <!-- end col-12 -->
</div>
<!-- end row -->


<script type="text/javascript">
	function install (name) {
		$("#name").val(name);
	}
</script>