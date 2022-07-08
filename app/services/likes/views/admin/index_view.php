<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                Останнє, що вподобали користувачі
                <div class="panel-heading-btn">
                    <a href="<?=SERVER_URL?>admin/wl_users" class="btn btn-info btn-xs">До користувачів</a>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Дата/час</th>
                                <th>Користувач</th>
                                <th>Розділ</th>
                                <th>Сторінка</th>
                                <th>Дія</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($likes) foreach ($likes as $like) { ?>
                                <tr>
                                    <td><?=date('d.m.y H:i', $like->date_update)?></td>
                                    <td><a href="<?=SERVER_URL?>admin/wl_users/<?=$like->user_email?>"><?=$like->user_name?></a></td>
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
    </div>
</div>