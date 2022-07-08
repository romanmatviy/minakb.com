var data;
function save (field, e, lang) {
    $('#saveing').css("display", "block");
    var value = '';
    if(e != false) value = e.value;
    else value = data;

    $.ajax({
        url: SITE_URL + "admin/wl_ntkd/save",
        type: 'POST',
        data: {
            alias: ALIAS_ID,
            content: CONTENT_ID,
            field: field,
            data: value,
            language: lang,
            additional_table : ADDITIONAL_TABLE,
            additional_table_id : ADDITIONAL_TABLE_ID,
            additional_fields : ADDITIONAL_FIELDS,
            json: true
        },
        success: function(res){
            if(res['result'] == false) {
                $.gritter.add({title:"Помилка!",text:res['error']});
            } else {
                language = '';
                if(lang) language = lang;
                $.gritter.add({title:field+' '+language,text:"Дані успішно збережено!"});
            }
            $('#saveing').css("display", "none");
        },
        error: function(){
            $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
            $('#saveing').css("display", "none");
        },
        timeout: function(){
            $.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
            $('#saveing').css("display", "none");
        }
    });
}

CKFinder.setupCKEditor( null, {
	basePath : SITE_URL + 'assets/ckfinder/',
	filebrowserBrowseUrl : SITE_URL + 'assets/ckfinder/ckfinder.html',
	filebrowserImageBrowseUrl : SITE_URL + 'assets/ckfinder/ckfinder.html?type=Images',
	filebrowserFlashBrowseUrl : SITE_URL + 'assets/ckfinder/ckfinder.html?type=Flash',
	filebrowserUploadUrl : SITE_URL + 'assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Files',
	filebrowserImageUploadUrl : SITE_URL + 'assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Images',
	filebrowserFlashUploadUrl : SITE_URL + 'assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Flash',
});

function saveText(lang)
{
	if(lang != false){
		data = CKEDITOR.instances['editor-'+lang].getData();
	} else {
		data = CKEDITOR.instances['editor'].getData();
	}
	save('text', false, lang);
}
function showEditTKD (lang) {
	if($('#tkd-'+lang).is(":hidden")){
		$('#tkd-'+lang).slideDown("slow");
	} else {
		$('#tkd-'+lang).slideUp("fast");
	}
}

if(ALIAS_FOLDER) {
    $(initFileUpload(0));
    $('#resizer').change(function () { initFileUpload(0); })
    $('#newMain').change(function () { initFileUpload(0); })
};

function initFileUpload(section_id) {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload-section-'+section_id).fileupload({
        url: SITE_URL+'admin/wl_photos/add/'+CONTENT_ID,
        formData: {
            ALIAS_ID : ALIAS_ID,
            ALIAS_FOLDER : ALIAS_FOLDER,
            SECTION_ID: section_id,
            PHOTO_FILE_NAME : PHOTO_FILE_NAME,
            PHOTO_TITLE : PHOTO_TITLE,
            additional_table : ADDITIONAL_TABLE,
            additional_table_id : ADDITIONAL_TABLE_ID,
            additional_fields : ADDITIONAL_FIELDS,
            resizer : resizer.checked ? 1 : 0,
            newMain : newMain.checked ? 1 : 0,
            json : true
        },
        autoUpload: true,
        acceptFileTypes: /(\.|\/)(jpe?g|png|webp|svg)$/i
    });

    $( "#PHOTOS-"+ section_id + " tbody.files" ).sortable({
      handle: ".sortablehandle",
      update: function( event, ui ) {
            $('#saveing').css("display", "block");
            $.ajax({
                url: SITE_URL+"admin/wl_photos/change_position",
                type: 'POST',
                data: {
                    alias: ALIAS_ID,
                    content: CONTENT_ID,
                    section_id: section_id,
                    id: ui.item.attr('id'),
                    position: ui.item.index(),
                    json: true
                },
                success: function(res){
                    if(res['result'] == false){
                        alert(res['error']);
                    }
                    $('#saveing').css("display", "none");
                },
                error: function(){
                    alert("Помилка! Спробуйте ще раз!");
                    $('#saveing').css("display", "none");
                },
                timeout: function(){
                    alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
                    $('#saveing').css("display", "none");
                }
            });
        }
    });
    $( "#PHOTOS-"+ section_id + " tbody.files" ).disableSelection();

    document.getElementById('PHOTOS-'+ section_id).onclick = function (event) {
        event = event || window.event;
        var target = event.target || event.srcElement;
        if(target.tagName == "IMG")
        {
            var link = target.src ? target.parentNode : target,
                options = {index: link, event: event},
                links = this.getElementsByTagName('a');
            blueimp.Gallery(links, options);
        }
    };
}

function savePhoto(id, e)
{
    $('#pea-saveing-'+id).css("display", "block");
    $.ajax({
        url: SITE_URL+"admin/wl_photos/save",
        type: 'POST',
        data: {
            photo: id,
            alias: ALIAS_ID,
            content: CONTENT_ID,
            name: e.name,
            title: e.value,
            additional_table : ADDITIONAL_TABLE,
            additional_table_id : ADDITIONAL_TABLE_ID,
            additional_fields : ADDITIONAL_FIELDS,
            json: true
        },
        success: function(res) {
            if(res['result'] == false) {
                alert(res['error']);
            } else {
                if(e.name == 'main') {
                    $('.PHOTO_MAIN').attr('disabled', false);
                    e.setAttribute('disabled', 'disabled');
                    PHOTO_MAIN = id;
                }
            }
            $('#pea-saveing-'+id).css("display", "none");
        },
        error: function(){
            alert("Помилка! Спробуйте ще раз!");
            $('#pea-saveing-'+id).css("display", "none");
        },
        timeout: function(){
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
            $('#pea-saveing-'+id).css("display", "none");
        }
    });
}

function deletePhoto(id){
	if (confirm("Ви впевнені, що хочете видалити фотографію? \nУВАГА, інформація відновленню НЕ ПІДЛЯГАЄ!")) {
		$('#pea-saveing-'+id).css("display", "block");
		$.ajax({
			url: SITE_URL+"admin/wl_photos/delete",
			type: 'POST',
			data: {
				photo: id,
                alias: ALIAS_ID,
                content: CONTENT_ID,
                ALIAS_FOLDER : ALIAS_FOLDER,
                additional_table : ADDITIONAL_TABLE,
	            additional_table_id : ADDITIONAL_TABLE_ID,
	            additional_fields : ADDITIONAL_FIELDS,
				json: true
			},
			success: function(res){
				if(res['result'] == false){
					alert(res['error']);
				} else {
                    $("#photo-"+id).remove();
                    if(res.main)
                    {
                        $('.PHOTO_MAIN').attr('disabled', false);
                        PHOTO_MAIN = res.main;
                        $('#photo-'+res.main+' button.PHOTO_MAIN').attr('disabled', 'disabled')
                    }
                }
			},
			error: function(){
                alert("Помилка! Спробуйте ще раз!");
                $('#pea-saveing-'+id).css("display", "none");
            },
            timeout: function(){
                alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
                $('#pea-saveing-'+id).css("display", "none");
            }
		});
	}
}


$("#modal-groupsTree").on("show.bs.modal", function(e) {
    $.ajax({
        url: ALIAS_ADMIN_URL+'_get_groupsTree?product='+CONTENT_ID,
        success: function(res){
            $("#modal-groupsTree").find(".modal-body").html(res);

            var to = false;
            $('#search').keyup(function () {
                if(to) { clearTimeout(to); }
                to = setTimeout(function () {
                    var v = $('#search').val();
                    $('#jstree').jstree(true).search(v);
                }, 250);
            });

            $('#jstree')
                .on("changed.jstree", function (e, data) {
                    $('#selected').val(data.selected);
                })
                .jstree(
                    {plugins: ["wholerow", "checkbox", "search"]}
                ); 
        },
        error: function(){
            alert("Помилка! Спробуйте ще раз!");
        },
        timeout: function(){
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
        }
    });
});


$("#modal-add-section").on("show.bs.modal", function(e) {
    var btn = $(e.relatedTarget),
        modal = $("#modal-add-section");
    modal.find("#section_position").val(btn.data('position'));
});

function addSection() {
    var modal = $("#modal-add-section"),
        position = modal.find("#section_position").val();
    $.ajax({
        url: SITE_URL+'admin/wl_sections/add',
        type: 'POST',
        data: {
            alias_id: modal.find("#alias_id").val(),
            content_id: modal.find("#content_id").val(),
            type: modal.find("#section_type").val(),
            name: modal.find("#section_name").val(),
            title: modal.find("#section_title").val(),
            access: modal.find("#section_access").val(),
            position: position
        },
        success: function(res){
            $("#modal-add-section").modal('hide');
            if(position == 0)
                $('.sections').append(res.html);
            if (res.js_init)
                eval(res.js_init);
            $(".sections").find('.editSection').change(function () {
                let section_id = this.dataset.section_id,
                    field = this.name,
                    value = $(this).val();
                wl_sections_set(section_id, field, value);
            });
        },
        error: function(){
            alert("Помилка! Спробуйте ще раз!");
        },
        timeout: function(){
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
        }
    });
}

$("#modal-config-section").on("show.bs.modal", function(e) {
    var btn = $(e.relatedTarget),
        modal = $("#modal-config-section");
    
    $.ajax({
        url: SITE_URL+'admin/wl_sections/get',
        type: 'POST',
        data: {
            section_id: btn.data('section_id')
        },
        success: function(section){
            if(section.id > 0)
            {
                let name = section.title;
                if (name == '')
                name = section.name;
                if (name == '')
                    name = 'section #' + section.id;
                modal.find('h4 strong').text(name);
                modal.find("#section_id").val(section.id);
                modal.find("[name=type]").val(section.type);
                modal.find("[name=name]").val(section.name);
                modal.find("[name=attr]").val(section.attr);
                modal.find("[name=access]").val(section.access);
            }
            else
            {
                alert('section_id error');
                $("#modal-config-section").modal('hide');
            }
        },
        error: function(){
            alert("Помилка! Спробуйте ще раз!");
            $("#modal-config-section").modal('hide');
        },
        timeout: function(){
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
            $("#modal-config-section").modal('hide');
        }
    });
});

$("#modal-delete-section").on("show.bs.modal", function(e) {
    var section_id = $("#modal-config-section").find('#section_id').val();
        name = $("#modal-config-section").find('h4 strong').text();
    $("#modal-delete-section").find('#section_id').val(section_id);
    $("#modal-delete-section").find('h4 strong').text(name);
});

$("section").find('.editSection').change(function () {
    let section_id = this.dataset.section_id,
        field = this.name,
        value = $(this).val();
    wl_sections_set(section_id, field, value);
})

$("#modal-config-section").find('input, select, textarea').change(function () {
    let section_id = $("#modal-config-section").find("#section_id").val(),
        field = this.name,
        value = $(this).val();
    wl_sections_set(section_id, field, value);
})

function wl_sections_set(section_id, field, value) {
    $.ajax({
        url: SITE_URL + 'admin/wl_sections/set',
        type: 'POST',
        data: {
            section_id: section_id,
            field: field,
            value: value
        },
        success: function (res) {
            if (res.update)
                $('#section_' + section_id).html(res.html);
            if (res.js_init)
                eval(res.js_init);
            $.gritter.add({ title: 'Дані збережено! / ' + res.name, text: field + ": " + value });
        },
        error: function () {
            alert("Помилка! Спробуйте ще раз!");
        },
        timeout: function () {
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
        }
    });
 }

function deleteSection() {
    let section_id = $("#modal-delete-section").find('#section_id').val();
    $.ajax({
        url: SITE_URL + 'admin/wl_sections/delete',
        type: 'POST',
        data: {
            section_id: section_id
        },
        success: function (res) {
            $("#modal-delete-section").modal('hide');
            $('#section_' + section_id).slideUp();
            $.gritter.add({ title: 'Секцію видалено!', text: 'Видалено всі мульмедійні дані в секції' });
        },
        error: function () {
            alert("Помилка! Спробуйте ще раз!");
        },
        timeout: function () {
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
        }
    });
 }