<div class="col-md-12">
    <div class="panel panel-inverse" data-sortable-id="profile-<?=$_SESSION['alias']->alias?>">
        <div class="panel-heading">
            <h4 class="panel-title"><?=$_SESSION['alias']->name?></h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
	            <thead>
	                <tr>
	                    <th>Дата/час додано</th>
	                    <th>Розділ</th>
	                    <th>Товар</th>
	                    <?php if($_SESSION['option']->saveToHistory) { ?>
	                    	<th>Статус</th>
	                    <?php } ?>
	                </tr>
	            </thead>
				<tbody>
	                <?php if($likes) foreach ($likes as $like) { ?>
	                    <tr>
	                        <td><?=date('d.m.y H:i', $like->date_add)?></td>
	                        <td><a href="<?=SITE_URL.'admin/'.$like->alias_uri?>"><?=$like->alias_name?></a></td>
	                        <td><a href="<?=SITE_URL.'admin/'.$like->alias_uri?>/search?id=<?=$like->content?>"><?=$like->page_name?></a></td>
	                        <?php if($_SESSION['option']->saveToHistory) { ?>
	                        	<td><?=($like->status) ? 'В порівнянні' : 'Скасовано' ?></td>
	                        <?php } ?>
	                    </tr>
	                <?php } ?>
	            </tbody>
			</table>
			<?php
			$this->load->library('paginator');
			echo $this->paginator->get();
			?>
        </div>
    </div>
</div>