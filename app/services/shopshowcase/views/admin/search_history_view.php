<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="table-responsive">
                <table class="table table-striped table-bordered nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>Артикул/назва товару</th>
                            <th>Де шукали</th>
                            <th>Знайдено результатів</th>
    						<th>Дата / Останній пошук</th>
                            <th>Переглядів за день</th>
                            <th>Клієнт</th>
                        </tr>
                    </thead>
                    <tbody>
    				<?php if($search_history) 
    					foreach($search_history as $search) { ?>
                        <tr>
                            <th><a href="/<?=$search->search_url?>?name=<?=$search->search_by?>" title="<?=$search->title?>"><?=$search->search_by?></a></th>
                            <td><?=$search->title?></td>
                            <th><?=($search->find >= 0) ? $search->find : 'Не відслідковано' ?></th>
                            <td><?=date("d.m.Y H:i", $search->last_view)?></td>
                            <td><?=$search->count_per_day?></td>
                            <td><?php if($search->user) { ?>
                                <a href="<?=SITE_URL?>admin/wl_users/<?=$search->user_email?>"><?=$search->user_name?></a>
                                <?php } else echo "Гість"; ?>
                            </td>
                        </tr>
                    <?php } ?>
    				</tbody>
    			</table>
    		</div>
    		<?php
            $this->load->library('paginator');
            echo $this->paginator->get();
            ?>
    	</div>
    </div>
</div>