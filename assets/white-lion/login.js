function googleSignIn(googleUser) {
    var auth2 = googleUser.getAuthResponse(true).access_token;
    if (auth2)
    {
        $("#divLoading").addClass('show');

        $('#authAlert').addClass('collapse');
        $.ajax({
            url: SERVER_URL+'login/google',
            type: 'POST',
            data: {
                redirect : redirect,
                accessToken: auth2,
                ajax: true
            },
            complete: function() {
                $("div#divLoading").removeClass('show');
            },
            success: function(res) {
                if (res['result'] == true) {
                    if(redirect)
                        window.location.href = redirect;
                    else
                        window.location.href = SITE_URL;
                } else {
                    $('#authAlert').removeClass('collapse');
                    $("#authAlertText").text(res['message']);
                }
            }
        })
    }
}

$('.facebook-login').click(function facebookSignUp() {
    FB.login(function(response) {
        if (response.authResponse) {
            $("#divLoading").addClass('show');
            var accessToken = response.authResponse.accessToken;
            FB.api('/me?fields=email', function(response) {
                if (response.email && accessToken) {
                    $('#authAlert').addClass('collapse');
                    $.ajax({
                        url: SERVER_URL+'login/facebook',
                        type: 'POST',
                        data: {
                            redirect : redirect,
                            accessToken: accessToken,
                            ajax: true
                        },
                        complete: function() {
                            $("div#divLoading").removeClass('show');
                        },
                        success: function(res) {
                            if (res['result'] == true) {
                                if(redirect)
                                    window.location.href = redirect;
                                else
                                    window.location.href = SITE_URL;
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
})