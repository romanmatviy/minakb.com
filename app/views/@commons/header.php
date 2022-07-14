<link rel="stylesheet" type="text/css"
    href="<?=SERVER_URL . 'assets/bootstrap/css/bootstrap.min.css'?>">

<header class="header">
    <div class="container d-flex">
        <a href="<?=SITE_URL?>" class="d-flex v-center logo">
            <img src="<?=SERVER_URL?>style/images/logo.svg"
                width="120" height="120" title="<?= SITE_NAME ?>">
            <span><?= $this->text('Міністерство акумуляторів', 0) ?>
                <br>
                minakb.com.ua
            </span>
        </a>
        <nav class="menu">

            <form action="<?=SITE_URL?>search" method="GET"
                class="inputs-bg" id="searchform">
                <input type="text" name="by" class="form-control" value=""
                    placeholder="<?= $this->text('Пошук'); ?>"
                    required="">
            </form>

            <!-- <a href="<?=SITE_URL?>"><?= $this->text('Головна', 0); ?></a> -->
            <!-- <a href="<?=SITE_URL?>about_us"><?= $this->text('Про нас', 0); ?></a> -->
            <!-- <a href="<?=SITE_URL?>contact"><?= $this->text('Контакти', 0); ?></a> -->

            <a href="<?=SITE_URL?>our_departaments"><?= $this->text('Наші відділення', 0); ?></a>
            <a href="<?=SITE_URL?>partners"><?= $this->text('Стати партнером', 0); ?></a>
            <a href="<?=SITE_URL?>discovery_branch"><?= $this->text('Відкриття
філіалу 2023', 0); ?>
            </a>

            <?php if ($this->userIs()) { ?>
            <a href="<?=SITE_URL?>profile"><?= $this->text('Кабінет', 0); ?></a>
            <?php if ($this->userCan()) { ?>
            <a href="<?=SITE_URL?>admin"><?= $this->text('Адмін', 0); ?></a>
            <?php } ?>
            <a href="<?=SITE_URL?>logout"><?= $this->text('Вийти', 0); ?></a>
            <?php } else { ?>
            <!-- <a href="<?=SITE_URL?>login"><?= $this->text('Увійти', 0); ?></a> -->
            <?php }
            //$this->load->function_in_alias('cart', '__show_minicart');
        ?>

            <!-- <div class="header-langs">
                <a href="<?=SITE_URL_UA?>"><?= $this->text('УКР', 0); ?></a> | <a
                class="ml-0" href="<?=SITE_URL_EN?>">ENG</a>
    </div> -->

    <a href="<?=SITE_URL?>likes"><?= $this->text('Обране', 0); ?></a>
    </nav>
    </div>
</header>

<div class="page-head content-top-margin">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-sm-7">
                <ol class="breadcrumb">
                    <li>
                        <a href="<?= SITE_URL?>"><?=$this->text('Головна', 0)?></a>
                    </li>
                    <li class="active"><?=$_SESSION['alias']->name?>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>