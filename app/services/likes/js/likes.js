function setLike() {
    $.ajax({
        url: LIKE_URL+'/setlike',
        type: 'POST',
        data: {
            alias: LIKE_alias,
            content: LIKE_content,
            ajax: true
        },
        success: function(res) {
            if(res)
            {
                if (res == 'no login')
                {
                    $('#like_no_login').slideDown();
                }
                else
                {
                    if(res.setLike)
                    {
                        $('#like-set-ok').slideDown();
                        $('#like-set-cancel').slideUp();
                        pageLikesFavicon.style.color = 'red';
                        $('#like_set_success').slideDown();
                    }
                    else if(res.cancel)
                    {
                        $('#like_set_success').slideUp();
                        $('#like_confirm').slideDown();
                    }
                    pageLikesCount.innerText = res.count;
                }
            }
            else
                alert('Error user like!');
        }
    })
}

function setCancel() {
    $.ajax({
        url: LIKE_URL+'/cancellike',
        type: 'POST',
        data: {
            alias: LIKE_alias,
            content: LIKE_content,
            ajax: true
        },
        success: function(res) {
            if(res)
            {
                if (res == 'no login')
                {
                    $('#like_no_login').slideDown();
                }
                else
                {
                    if(res.cancelLike)
                    {
                        $('#like_confirm').slideUp();
                        $('#like-set-ok').slideUp();
                        $('#like-set-cancel').slideDown();
                        $('#like_set_success').slideDown();
                        pageLikesFavicon.style.color = 'gray';
                    }
                    pageLikesCount.innerText = res.count;
                }
            }
            else
                alert('Error user like!');
        }
    })
}

function likeSignUp() {
    $('#like-ajax-error').slideUp();
    var ajax_error = '';
    var name = $('#like-name').val();
    name = name.trim();
    if(name == '')
        ajax_error = LIKE_ERROR_empty_name;
    var email = $('#like-email-signup').val();
    email = email.trim();
    if(email == '')
    {
        if(ajax_error != '')
            ajax_error += '<br>';
        ajax_error += LIKE_ERROR_empty_email;
    }
    if(ajax_error != '')
    {
        $('#like-ajax-error p').html(ajax_error);
        $('#like-ajax-error').slideDown();
    }
    if(name != '' && email != '' && LIKE_alias > 0 && LIKE_content > 0)
    {
        $.ajax({
            url: LIKE_URL+'/signup',
            type: 'POST',
            data: {
                name: name,
                email: email,
                alias: LIKE_alias,
                content: LIKE_content,
                ajax: true
            },
            success: function(res) {
                if(res)
                {
                    if (res.result)
                    {
                        $('#like_no_login').slideUp();
                        $('#like_set_success').slideDown();
                        $('#like_success_signup').slideDown();

                        if(res.setLike)
                        {
                            $('#like-set-ok').slideDown();
                            $('#like-set-cancel').slideUp();
                            pageLikesFavicon.style.color = 'red';
                        }
                        else if(res.cancel)
                        {
                            $('#like_set_success').slideUp();
                            $('#like_confirm').slideDown();
                        }
                        else
                        {
                            $('#like-set-ok').slideUp();
                            $('#like-set-cancel').slideDown();
                            pageLikesFavicon.style.color = 'gray';
                        }
                        pageLikesCount.innerText = res.count;
                    }
                    else
                    {
                        $('#like-ajax-error p').html(res.message);
                        $('#like-ajax-error').slideDown();
                    }
                }
                else
                    alert('Error user like!');
            }
        })
    }
    return false;
}

function likeLogin() {
    $('#like-ajax-error').slideUp();
    var ajax_error = '';
    var emailtel = $('#like-email-login').val();
    emailtel = emailtel.trim();
    if(emailtel == '')
        ajax_error = LIKE_ERROR_empty_emailtel;
    var password = $('#like-password').val();
    password = password.trim();
    if(password == '')
    {
        if(ajax_error != '')
            ajax_error += '<br>';
        ajax_error += LIKE_ERROR_empty_password;
    }
    if(ajax_error != '')
    {
        $('#like-ajax-error p').html(ajax_error);
        $('#like-ajax-error').slideDown();
    }
    if(emailtel != '' && password != '' && LIKE_alias > 0 && LIKE_content > 0)
    {
        $.ajax({
            url: LIKE_URL+'/login',
            type: 'POST',
            data: {
                email: emailtel,
                password: password,
                alias: LIKE_alias,
                content: LIKE_content,
                ajax: true
            },
            success: function(res) {
                if(res)
                {
                    if (res.result)
                    {
                        $('#like_no_login').slideUp();

                        if(res.setLike)
                        {
                            $('#like-set-ok').slideDown();
                            $('#like-set-cancel').slideUp();
                            pageLikesFavicon.style.color = 'red';
                            $('#like_set_success').slideDown();
                        }
                        else if(res.cancel)
                        {
                            $('#like_confirm').slideDown();
                        }
                        else
                        {
                            $('#like-set-ok').slideUp();
                            $('#like-set-cancel').slideDown();
                            pageLikesFavicon.style.color = 'gray';
                        }
                        pageLikesCount.innerText = res.count;
                    }
                    else
                    {
                        $('#like-ajax-error p').html(res.message);
                        $('#like-ajax-error').slideDown();
                    }
                }
                else
                    alert('Error user like!');
            }
        })
    }
    return false;
}

$('#like_set_success .closeSuccess').click(function() {
    $('#like_set_success').slideUp();
});

jQuery.fn.centerLike = function () {
    this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2)) + "px");
    this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2)) + "px");
    return this;
}

$(function() {
    $('#like_set_success, #like_no_login, #like_confirm').centerLike();
});

function facebookSignUp() {
    FB.login(function(response) {
        if (response.authResponse) {
            $("#divLoading").addClass('show');
            var accessToken = response.authResponse.accessToken;
            FB.api('/me?fields=email', function(response) {
                if (response.email && accessToken) {
                    $('#authAlert').addClass('collapse');
                    $.ajax({
                        url: SITE_URL + 'signup/facebook',
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
                                $('#like_no_login').slideUp();
                                setLike();
                            } else {
                                $('#like-ajax-error p').html(res.message);
                                $('#like-ajax-error').slideDown();
                            }
                        }
                    })
                } else {
                    $("div#divLoading").removeClass('show');
                    $('#like-ajax-error p').html('Для авторизації потрібен e-mail');
                    $('#like-ajax-error').slideDown();
                    FB.api("/me/permissions", "DELETE");
                }
            });
        } else {
            $("div#divLoading").removeClass('show');
        }

    }, { scope: 'email' });
}