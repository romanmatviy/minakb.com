<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?=SITE_URL?>admin/wl_mail_template/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати новий шаблон</a>
                </div>
                <h4 class="panel-title">Шаблони листів / розсилки</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th></th>
                                <th>Від</th>
                                <th>До</th>
                                <th>Мульмимовність</th>
                                <th>Зберегти в історію</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($mailTemplates) foreach ($mailTemplates as $template) { ?>
                            <tr>
                                <td><a href="<?= SITE_URL.'admin/wl_mail_template/'. $template->id ?>"> <?= $template->id ?></a></td>
                                <td><a href="<?= SITE_URL.'admin/wl_mail_template/'. $template->id ?>" class="btn btn-xs btn-info"> <?= $template->title ?? 'Дивитися' ?></a></td>
                                <td><?= $template->from ?></td>
                                <td><?= $template->to ?></td>
                                <td><?= $template->multilanguage ?></td>
                                <td><?= $template->savetohistory ?></td>
                                <td><button onclick="deleteTemplate(<?= $template->id ?>)" class="btn btn-xs btn-danger"> Видалити</button></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($_SESSION['option']->sendEmailSaveHistory) { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h4 class="panel-title">Архів. Останні 30 відправок</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Дата</th>
                                    <th>Від</th>
                                    <th>До</th>
                                    <th>Тема</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($history = $this->db->getAllData('wl_mail_history', 'id DESC LIMIT 30')) {
                                    foreach ($history as $row) { ?>
                                    <tr>
                                        <td><a href="<?= SITE_URL.'admin/wl_mail_template/history/'. $row->id ?>" class="btn btn-xs btn-info"> Детально (#<?= $row->id ?>)</a></td>
                                        <td><?= date('d.m.Y H:i', $row->date) ?> / <strong><?=$row->send_email ? 'Відправлено' : 'Очікує відправки'?></strong></td>
                                        <td><?= $row->from ?></td>
                                        <td><?= $row->to ?></td>
                                        <td><?= $row->subject ?></td>
                                    </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Архів пустий</td>
                                        </tr>
                                    <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<script>
    function deleteTemplate(id) {
        if(confirm('Видалити розсилку?')){
            $.ajax({
                url : '<?= SITE_URL?>admin/wl_mail_template/deleteTemplate',
                method : 'POST',
                data : {
                    id: id
                },
                success : function (res) {
                    window.location.href = '<?= SITE_URL?>admin/wl_mail_template';
                }
            })
        }
    }
</script>