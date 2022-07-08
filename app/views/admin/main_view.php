<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?=SITE_URL.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs">На сторінку</a>
                </div>

                <h5 class="panel-title">
                    Головна сторінка
                </h5>
            </div>

            <?php
            $PHOTO_FILE_NAME = $_SESSION['alias']->alias;
            require '__edit_page.php'; ?>
        </div>
    </div>
</div>