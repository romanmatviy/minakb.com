<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                </div>
                <h4 class="panel-title">Коментарі</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <form action="<?=SITE_URL?>admin/wl_comments/statusMultiedit" method="POST">
                        <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th width="70px" nowrap>ID</th>
                                    <th> Розділ</th>
                                    <th> Відповіді</th>
                                    <th> Коментатор</th>
                                    <th> Коментар</th>
                                    <th> Статус</th>
                                    <th> Додано</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($comments) foreach ($comments as $comment) {
                                    $color = '';
                                    if($comment->status == 2 || $comment->status == 3)
                                        $color = 'warning';
                                    else if($comment->status == 4)
                                        $color = 'danger';
                                    ?>

                                    <tr class="<?=$color?>">
                                        <td>
                                            <input type="checkbox" name="row[]" value="<?=$comment->id?>">
                                        </td>
                                        <td>
                                            <a href="<?=SITE_URL?>admin/wl_comments/<?=$comment->id?>"><i class="fa fa-pencil"></i> <?=$comment->id?></a>
                                        </td>
                                        <td>
                                            <a href="<?=SITE_URL.$comment->link?>#comment-<?=$comment->id?>" target="_blank" style="text-decoration: none;">
                                                <i class="fa fa-eye"></i> 
                                            </a> 
                                            <a href="<?=SITE_URL?>admin/wl_comments/<?=$comment->id?>">
                                                <?=$this->data->getShortText($comment->page_name, 30); ?>
                                            </a>                                
                                        </td>
                                        <td><?=$comment->reply?></td>
                                        <td><?=$comment->user_name?> / <?=$comment->user_email?></td>
                                        <td><a href="<?=SITE_URL?>admin/wl_comments/<?=$comment->id?>"><?=$this->data->getShortText($comment->comment, 40) ?>..</a></td>
                                        <td><?php switch ($comment->status) {
                                            case 1:
                                                echo('Активний');
                                                break;
                                            case 2:
                                                echo('Новий активний');
                                                break;
                                            case 3:
                                                echo('Чекає на модерацію');
                                                break;
                                            case 4:
                                                echo('Відключено');
                                                break;
                                        }?> </td>
                                        <td><?=date("d.m.Y H:i", $comment->date_add)?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <?php $statuses = array(1 => 'Активний', 2 => 'Новий активний', 3 => 'Чекає на модерацію', 4 => 'Відключено');
                                foreach ($statuses as $key => $name) {
                                    echo "<option value='{$key}'>{$name}</option>";
                                }
                                 ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-sm btn-success" >Зберегти</button>
                        </div>
                        <div class="col-md-8">
                            <?php
                            $this->load->library('paginator');
                            $this->paginator->style('ul', 'pagination m-0');
                            echo $this->paginator->get();
                            ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>