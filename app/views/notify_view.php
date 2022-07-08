<main class="container <?=$_SESSION['alias']->alias?>">
    <?php
    if(empty($errors) && !empty($_SESSION['notify']->errors))
        $errors = $_SESSION['notify']->errors;
    if(empty($success) && !empty($_SESSION['notify']->success))
        $success = $_SESSION['notify']->success;
    if(isset($_SESSION['notify']))
        unset($_SESSION['notify']);

    if(!empty($errors)): ?>
       <div class="alert alert-danger">
            <h4><?=$title ?? $this->text('Помилка!', 0)?></h4>
            <p><?=$errors?></p>
            <?php if(!isset($show_btn) || $show_btn) { ?>
            <p class="mt-15">
                <?php if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '' && $_SERVER['HTTP_REFERER'] != SITE_URL){ ?>
                    <a class="btn btn-warning" href="<?=$_SERVER['HTTP_REFERER']?>"><?=$this->text('Повернутися назад!', 0)?></a>
                <?php } ?>
                <a class="btn btn-success" href="<?=SITE_URL?>"><?=$this->text('На головну!', 0)?></a>
            </p>
            <?php } ?>
        </div>
    <?php elseif(!empty($success)): ?>
        <div class="alert alert-success">
            <h4><?=$title ?? $this->text('Успіх!', 0)?></h4>
            <p><?=$success?></p>
            <?php if(!isset($show_btn) || $show_btn) { ?>
            <p class="mt-15">
                <?php if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '' && $_SERVER['HTTP_REFERER'] != SITE_URL){ ?>
                    <a class="btn btn-warning" href="<?=$_SERVER['HTTP_REFERER']?>"><?=$this->text('Повернутися назад!', 0)?></a>
                <?php } ?>
                <a class="btn btn-info" href="<?=SITE_URL?>"><?=$this->text('На головну!', 0)?></a>
            </p>
            <?php } ?>
        </div>
    <?php endif; ?>
</main>