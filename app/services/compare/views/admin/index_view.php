<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                Товари, що користувачі додали до порівняння
                <div class="panel-heading-btn">
                    <?php if($_SESSION['option']->saveToHistory) {
                        if(!isset($_GET['all'])) { ?>
                            <a href="?all" class="btn btn-warning btn-xs">Включно зі скасованими</a>
                        <?php } else { ?>
                            <a href="<?=SERVER_URL?>admin/<?=$_SESSION['alias']->alias?>" class="btn btn-success btn-xs">Тільки активні</a>
                        <?php }
                    } ?>
                    <a href="<?=SERVER_URL?>admin/wl_users" class="btn btn-info btn-xs">До користувачів/клієнтів</a>
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
                                <?php if(isset($_GET['all'])) { ?>
                                    <th>Статус</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($likes) foreach ($likes as $like) { ?>
                                <tr <?=$like->status == 0 ? 'class="warning"' : ""?>>
                                    <td><?=date('d.m.y H:i', $like->date_add)?></td>
                                    <td><a href="<?=SERVER_URL?>admin/wl_users/<?=$like->user_email?>#tabs-<?=$_SESSION['alias']->alias?>"><?=$like->user_name?></a></td>
                                    <td><a href="<?=SITE_URL.'admin/'.$like->alias_uri?>"><?=$like->alias_name?></a></td>
                                    <td><a href="<?=SITE_URL.'admin/'.$like->alias_uri?>/search?id=<?=$like->content?>"><?=$like->page_name?></a></td>
                                    <?php if(isset($_GET['all'])) { ?>
                                        <td><?=($like->status) ? 'В порівнянні' : 'Скасовано' ?></td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="pull-right">Всіх: <strong><?=$_SESSION['option']->paginator_total?></strong></div>
                    <?php
                    $this->load->library('paginator');
                    echo $this->paginator->get();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>