<div class="col-md-12">
    <div class="panel panel-inverse" data-sortable-id="profile-like">
        <div class="panel-heading">
            <h4 class="panel-title"><?=$_SESSION['alias']->name?></h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered ">
	            <thead>
	                <tr>
	                    <th>Дата/час додано</th>
	                    <th>Дата/час оновлено</th>
	                    <th>Розділ</th>
	                    <th>Сторінка</th>
	                    <th>Дія</th>
	                </tr>
	            </thead>
				<tbody>
	                <?php if($likes) foreach ($likes as $like) { ?>
	                    <tr>
	                        <td><?=date('d.m.y H:i', $like->date_add)?></td>
	                        <td><?=date('d.m.y H:i', $like->date_update)?></td>
	                        <td><?=$like->alias_name?></td>
	                        <td>
	                            <?php if($page = $this->load->function_in_alias($like->alias, '__get_Search', $like->content, true))
	                                {
	                                    echo('<a href="'.SERVER_URL.$page->edit_link.'"><i class="fa fa-edit"></i></a> ');
	                                    echo('<a href="'.SERVER_URL.$page->link.'" target="_blank">'.$like->page_name.'</a>');
	                                }
	                                else
	                                    echo $like->page_name;
	                            ?>
	                        </td>
	                        <td><?=($like->status) ? 'Вподобано' : 'Скасовано' ?></td>
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