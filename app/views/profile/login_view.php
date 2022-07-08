<link rel="stylesheet" type="text/css" href="<?=SERVER_URL?>style/login.css">

<?php if($_SESSION['option']->facebook_initialise) { ?>
    <script>
    window.fbAsyncInit = function() {
        <?php $this->load->library('facebook'); ?>
        FB.init({
          appId      : '<?=$this->facebook->getAppId()?>',
          cookie     : true,
          xfbml      : true,
          version    : 'v3.1'
        });
    };

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function facebookSignUp() {
        FB.login(function(response) {
            if (response.authResponse) {
                $("#divLoading").addClass('show');
                var accessToken = response.authResponse.accessToken;
                FB.api('/me?fields=email', function(response) {
                    if (response.email && accessToken) {
                        $('#authAlert').addClass('collapse');
                        $.ajax({
                            url: '<?=SITE_URL?>signup/facebook',
                            type: 'POST',
                            data: {
                                accessToken: accessToken,
                                ajax: true
                            },
                            complete: function() {
                                $("div#divLoading").removeClass('show');
                            },
                            success: function(res) {
                                if (res['result'] == true) {
                                    window.location.href = '<?=SITE_URL?>profile';
                                } else {
                                    $('#authAlert').removeClass('collapse');
                                    $("#authAlertText").text(res['message']);
                                }
                            }
                        })
                    } else {
                        $("div#divLoading").removeClass('show');
                        $("#clientError").text('Для авторизації потрібен e-mail');
                        setTimeout(function(){$("#clientError").text('')}, 5000);
                        FB.api("/me/permissions", "DELETE");
                    }
                });
            } else {
                $("div#divLoading").removeClass('show');
            }

        }, { scope: 'email' });
    }
    </script>
<?php } ?>

<main class="container <?=($_SESSION['alias']->alias == 'signup')?'right-panel-active' : ''?>" id="login-container">
    <div class="form-container sign-up-container">
        <?php if($_SESSION['alias']->alias == 'signup') {
        if(!empty($_SESSION['notify']->errors)): ?>
           <div class="alert alert-danger">
                <span class="close" data-dismiss="alert">×</span>
                <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Помилка!', 0)?></h4>
                <p><?=$_SESSION['notify']->errors?></p>
            </div>
        <?php elseif(!empty($_SESSION['notify']->success)): ?>
            <div class="alert alert-success">
                <span class="close" data-dismiss="alert">×</span>
                <h4><i class="fa fa-check"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Успіх!', 0)?></h4>
                <p><?=$_SESSION['notify']->success?></p>
            </div>
        <?php endif; unset($_SESSION['notify']); } ?>
        <form action="<?=SITE_URL?>signup/process" method="POST" id="formSignUp">
            <h1><?=$this->text('Реєстрація')?></h1>
            <?php if($_SESSION['option']->facebook_initialise || $this->googlesignin->clientId) { ?>
                <div class="social-container">
                    <?php if($_SESSION['option']->facebook_initialise) { ?>
                        <a href="#" class="social facebook-login" title="<?=$this->text('Швидка реєстрація за допомогою facebook', 4)?>"><i class="fab fa-facebook-f"></i></a>
                    <?php } if($this->googlesignin->clientId) { ?>
                        <a href="#" class="social google-login" title="<?=$this->text('Швидкий вхід за допомогою google', 4)?>"><i class="fab fa-google"></i></a>
                    <?php } ?>
                </div>
                <span>або за допомогою email та паролю</span>
            <?php } ?>
            <div class="flex wrap">
                <input name="first_name" type="text" value="<?=$this->data->re_post('first_name')?>" placeholder="<?=$this->text('Ім\'я', 5)?>" required />
                <input name="last_name" type="text" value="<?=$this->data->re_post('last_name')?>" placeholder="<?=$this->text('Прізвище', 5)?>" required />
            </div>
            <div class="flex wrap">
                <input name="email" type="email" value="<?=$this->data->re_post('email')?>" placeholder="Email" required />
                <input name="phone" type="text" value="<?=$this->data->re_post('phone')?>" placeholder="<?=$this->text('Контактний телефон', 5)?>" required minlength="19"/>
            </div>
            <div class="flex wrap">
                <input name="password" type="password" value="<?=$this->data->re_post('password')?>" class="form-control" placeholder="<?=$this->text('Пароль', 4)?>" required />
                <input name="re-password" type="password" class="form-control" placeholder="<?=$this->text('Повторіть пароль', 5)?>" required />
            </div>
            <span><?=$this->text('*пороль має містити від 5 до 20 символів', 5)?></span>

            <?php
                $this->load->library('recaptcha');
                $this->recaptcha->form_v3($this->text('Зареєструватися', 5), 'formSignUp', 'mt-15');
            ?>
        </form>
    </div>
    <div class="form-container sign-in-container">
        <?php if($_SESSION['alias']->alias == 'login') {
        if(!empty($_SESSION['notify']->errors)): ?>
           <div class="alert alert-danger">
                <span class="close" data-dismiss="alert">×</span>
                <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Помилка!', 0)?></h4>
                <p><?=$_SESSION['notify']->errors?></p>
            </div>
        <?php elseif(!empty($_SESSION['notify']->success)): ?>
            <div class="alert alert-success">
                <span class="close" data-dismiss="alert">×</span>
                <h4><i class="fa fa-check"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Успіх!', 0)?></h4>
                <p><?=$_SESSION['notify']->success?></p>
            </div>
        <?php endif; unset($_SESSION['notify']); } ?>
        <form action="<?=SITE_URL?>login/process" method="POST" id="formLogin">
            <h1><?=$this->text('Увійти', 4)?></h1>
            <?php if($_SESSION['option']->facebook_initialise || $this->googlesignin->clientId) { ?>
                <div class="social-container">
                    <?php if($_SESSION['option']->facebook_initialise) { ?>
                        <a href="#" class="social facebook-login" title="<?=$this->text('Швидка реєстрація за допомогою facebook', 4)?>"><i class="fab fa-facebook-f"></i></a>
                    <?php } if($this->googlesignin->clientId) { ?>
                        <a href="#" class="social google-login" title="<?=$this->text('Швидкий вхід за допомогою google', 4)?>"><i class="fab fa-google"></i></a>
                    <?php } ?>
                </div>
                <span><?=$this->text('або за допомогою email та паролю', 4)?></span>
            <?php } ?>
            <?php if(isset($_GET['redirect']) || $this->data->re_post('redirect')) { ?>
                <input type="hidden" name="redirect" value="<?=$this->data->re_post('redirect', $this->data->get('redirect'))?>">
            <?php } ?>
            <input type="email" name="email" value="<?=$this->data->re_post('email')?>" placeholder="Email" required />
            <input type="password" name="password" placeholder="<?=$this->text('Пароль', 4)?>" required />
            <a href="<?=SITE_URL?>reset"><?=$this->text('Забули пароль?', 4)?></a>
            <?php $this->recaptcha->form_v3($this->text('Увійти', 4), 'formLogin'); ?>
        </form>
    </div>
    <div class="overlay-container m-hide">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1><?=$this->text('Вже зареєстровані?', 4)?></h1>
                <p><?=$this->text('увійти за допомогою email та паролю', 4)?></p>
                <button class="ghost hexa" id="signIn"><?=$this->text('Увійти', 4)?></button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1><?=$this->text('Реєстрація', 5)?></h1>
                <p><?=$this->text('Вкажіть свої персональні дані (ім\'я, емейл, телефон) та розпочнімо співпрацю з', 5)?> <span class="GreatVibes">La mûre</span></p>
                <button class="ghost hexa" id="signUp"><?=$this->text('Зареєструватися', 5)?></button>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript">
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('login-container');

    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });

    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    });

    <?php $this->load->js('assets/jquery.mask.min.js');
    if(!empty($_GET['redirect']) || $this->data->re_post('redirect')) {
        echo 'var redirect = "'.$this->data->re_post('redirect', $this->data->get('redirect')).'";';
    } else echo "var redirect = false;"; ?>
    
    window.onload = function() {
        $('.alert .close').click(function(){
            $('.alert').hide();
        });

        var mask_options =  {
          onKeyPress: function(cep, e, field, options) {
            mask = '+00 (000) 000-00-00';
            if(cep == '+')
                field.mask(mask, mask_options);
            else if(cep.length > 3)
            {
                cep = cep.substr(0, 3);
                if(cep == '+38')
                    $('input[name=phone]').mask('+38 (000) 000-00-00', mask_options);
                else
                    field.mask(mask, mask_options);
            }
        }};
    $('input[name=phone]').mask('+38 (000) 000-00-00', mask_options);
    };
</script>
<?php if($_SESSION['option']->userSignUp && ($_SESSION['option']->facebook_initialise || $this->googlesignin->clientId)) {
    if($this->googlesignin->clientId)
        echo '<script src="https://apis.google.com/js/platform.js" async defer></script>';
    $this->load->js('assets/white-lion/login.js');
} ?>