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

<main class="container <?=($_SESSION['alias']->alias == 'reset')?'right-panel-active' : ''?>" id="login-container">
    <div class="form-container sign-up-container">
        <?php if(!empty($_SESSION['notify']->errors)): ?>
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
        <?php endif; unset($_SESSION['notify']); ?>
        <form action="<?=SITE_URL?>reset/process" method="POST" id="formReset">
            <h1><?=$this->text('Відновлення паролю')?></h1>
            <input name="email" type="email" value="<?=$this->data->re_post('email')?>" placeholder="Email" required />
            <br>
            <?php
                $this->load->library('recaptcha');
                $this->recaptcha->form_v3($this->text('Продовжити відновлення'), 'formReset');
            ?>
        </form>
    </div>
    <div class="form-container sign-in-container">
        <form action="<?=SITE_URL?>login/process" method="POST" onsubmit="$('#divLoading').addClass('show');">
            <h1><?=$this->text('Вхід')?></h1>
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
            <input type="email" name="email" value="<?=$this->data->re_post('email')?>" placeholder="Email" required />
            <input type="password" name="password" placeholder="<?=$this->text('password', 4)?>" required />
            <a href="<?=SITE_URL?>reset" id="resetBtn"><?=$this->text('Забули пароль?', 4)?></a>
            <button type="submit" class="hexa"><?=$this->text('Увійти', 4)?></button>
        </form>
    </div>
    <div class="overlay-container m-hide">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1><?=$this->text('Вже зареєстровані?', 4)?></h1>
                <p><?=$this->text('увійти за допомогою email та паролю', 4)?></p>
                <button class="ghost" id="signIn"><?=$this->text('Увійти', 4)?></button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1><?=$this->text('Відновлення паролю')?></h1>
                <p><?=$this->text('Відновити доступ до кабінету користувача')?></p>
                <button class="ghost" id="signUp"><?=$this->text('Забув пароль')?></button>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript">
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('login-container');

    resetBtn.addEventListener('click', () => {
        event.preventDefault();
        container.classList.add("right-panel-active");
    });

    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });

    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    });

    <?php if(!empty($_GET['redirect']) || $this->data->re_post('redirect')) {
        echo 'var redirect = "'.$this->data->re_post('redirect', $this->data->get('redirect')).'";';
    } else echo "var redirect = false;"; ?>
    
    window.onload = function() {
        $('.alert .close').click(function(){
            $('.alert').hide();
        });
    };
</script>
<?php if($_SESSION['option']->userSignUp && ($_SESSION['option']->facebook_initialise || $this->googlesignin->clientId)) {
    if($this->googlesignin->clientId)
        echo '<script src="https://apis.google.com/js/platform.js" async defer></script>';
    $this->load->js('assets/white-lion/login.js');
} ?>