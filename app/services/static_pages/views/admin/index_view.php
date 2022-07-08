<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?=SITE_URL.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs">На сторінку</a>
                </div>

                <h5 class="panel-title">
                    Додано: <?=date('d.m.Y H:i', $page->date_add)?>
                    Редаговано: <?=date('d.m.Y H:i', $page->date_edit)?>
                </h5>
            </div>

            <?php
            $PHOTO_FILE_NAME = $_SESSION['alias']->alias;
            $ADDITIONAL_TABLE = $_SESSION['service']->table;
            $ADDITIONAL_TABLE_ID = $page->id;
            $ADDITIONAL_FIELDS = 'author_edit=>user,date_edit=>time';
            require APP_PATH.'views/admin/__edit_page.php'; ?>
        </div>
    </div>
</div>