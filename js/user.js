$(function () {
    $( "#tabs" ).tabs();

    $('#fileupload').fileupload({
        url: SERVER_URL+"profile/upload_avatar",
        autoUpload: true,
        acceptFileTypes: /(\.|\/)(jpe?g|png)$/i,
        start:function () {
            $("#photo-block #loading").show();
        },
        complete:function () {
            $("#photo-block #loading").hide();
        }
    });
});

function show_image (file) {
    var files = file.files;
    var file = files[0];
    photo.file = file;
    var reader = new FileReader();

    reader.onload = (function(aImg) {
        return function(e) {
            aImg.src = e.target.result;
        };
    })(photo);

    reader.readAsDataURL(file);
}

$('main #tabs table tr i.right').click(function (){
    var e = $(this);
    var text = this.parentElement.innerText;
    required = e.data('required');
    e.parent().empty().append($('<input/>', {name: e.data('name'), type: 'text', value: text, required: required}));
    $('input[name=phone]').mask('+38 (000) 000-00-00');

    $("main #tabs #main button.hide").removeClass('hide');
})

function facebookSignUp() {
    FB.login(function(response) {
        if (response.authResponse) {
            $("#divLoading").addClass('show');
            var accessToken = response.authResponse.accessToken;
            FB.api('/me?fields=email', function(response) {
                if (response.email && accessToken) {
                    $('#authAlert').addClass('collapse');
                    $.ajax({
                        url: SITE_URL + 'profile/facebook',
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
                                location.reload();
                            } else {
                                $('#authAlert').removeClass('collapse');
                                $("#authAlertText").text(res['message']);
                            }
                        }
                    })
                } else {
                    $("div#divLoading").removeClass('show');
                    $("#clientError").text('Для авторизації потрібен e-mail');
                    setTimeout(function(){ $("#clientError").text('') }, 5000);
                    FB.api("/me/permissions", "DELETE");
                }
            });
        }
        else
            $("div#divLoading").removeClass('show');
    }, { scope: 'email' });
    return false;
}