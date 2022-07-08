<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$template->id?>" class="btn btn-warning btn-xs"><i class="fa fa-ravelry"></i> До розсилки на основі шаблону</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><i class="fa fa-list"></i> До всіх шаблонів</a>
            	</div>
                <h4 class="panel-title">Отримувачі <strong><?=$template->name?></strong></h4>
            </div>
            <div class="panel-body">
				<h4>Отримувачі: <?= ($mails) ? count($mails) : 0 ?></h4>
				<?php $list = [];
				if($userTypes = $this->db->getAllDataByFieldInArray('wl_user_types', ['id' => $template->to_user_types]))
					foreach ($userTypes as $type)
						$list[$type->id] = $type->title;
					echo implode(', ', $list);
				 ?>
				<hr>
				<?php if($mails)
					foreach ($mails as $i => $m) 
						echo ($i + 1).'. <strong>'.$m->email.'</strong> '.$m->name.' ('.$list[$m->type].') <br>';
				?>
             </div>
         </div>
	</div>
</div>