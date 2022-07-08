<div class="row">
	<div class="col-md-12 ui-sortable">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                 </div>
                <h4 class="panel-title">Нова покупка</h4>
            </div>
            <div class="panel-body">
				<div id="wizard">
					<ol>
						<li>Покупець</li>
						<li>Продукти</li>
					</ol>
					<!-- begin wizard step-1 -->
					<div>
                        <fieldset>
                            <div class="row">
                            	<input type="hidden" id="userId" value="0">
                                <div class="col-md-6">
                                	<legend class="text-center">Існуючий покупець</legend>
                                	<label id="userError" hidden>Такого користувача не існує</label>
									<div class="form-group">
										<label>Введіть прізвище або email або телефон</label>
										<div class="input-group">
											<input type="text" id="userInfo" class="form-control">
											<span class="input-group-btn">
									        	<button class="btn btn-success" type="button" onclick="findUser()">Знайти</button>
									     	</span>
										</div>
									</div>
									<div class="panel panel-default" id="userTable" hidden>
									    <table class="table">
									        <thead>
									            <tr>
									                <th>#</th>
									                <th>Прізвище та ім'я</th>
									                <th>Email, Телефон</th>
									                <th></th>
									            </tr>
									        </thead>
									        <tbody>
									        </tbody>
									    </table>
									</div>
                                </div>
                                <div class="col-md-6" style="border-left: 1px solid #e2e7eb;">
                                	<legend class="text-center">Новий покупець</legend>
                                	<label id="newUserError" hidden></label>
									<div class="form-group">
										<label>Прізвище та ім'я</label>
										<input type="text" id="userName" class="form-control">
									</div>
									<div class="form-group">
										<label>Еmail</label>
										<input type="email" id="userEmail" class="form-control">
									</div>
									<div class="form-group">
										<label>Телефон</label>
										<input type="text" id="userPhone" class="form-control">
									</div>
									<div class="form-group text-center">
										<button class="btn btn-success" type="button" onclick="saveNewUser()">Зберегти</button>
									</div>
                                </div>
                                <div class="col-md-12 text-center">
                                	<button class="btn btn-lg btn-success" onclick="setUser(0, <?= $_SESSION['option']->new_user_type?>)">Гостьовий режим</button>
                                </div>
                            </div>
						</fieldset>
					</div>
					<!-- end wizard step-1 -->
					<!-- begin wizard step-2 -->
					<div>
						<fieldset>
							<legend class="text-center">Додати товар</legend>
                            <?php require_once 'tabs/_tabs-add_product.php'; ?>
						</fieldset>
					</div>
					<!-- end wizard step-2 -->
				</div>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>
<?php $_SESSION['alias']->js_load[] = 'assets/bwizard/bwizard.js'; ?>

<script>
	document.addEventListener('DOMContentLoaded', function(){
	   var bwizard = $("#wizard").bwizard({
	   		clickableSteps: false,
		   	backBtnText: "Назад",
		   	nextBtnText: "Далі"
	   });

	   $("#userInfo").keypress(function (e) {
	    	if(e.keyCode == 13){
	    		findUser();
	    	}
	    })

	   $("#newProduct").show();

	});

	function findUser() {
		$("#userError").hide();
		var userInfo = $("#userInfo").val().trim();
		if(userInfo != '')
		{
			$.ajax({
				url: "<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/findUser'?>",
				type:"POST",
				data:{
					"userInfo":userInfo
				},
				success:function (res) {
					if(res['result'] == true){
						$("#userTable").show();
						$("#userTable>table>tbody").empty();

						$.each(res['user'], function (index,value) {
							$("#userTable>table>tbody").append("<tr><td>"+value.id+"</td><td><strong>"+value.name+"</strong><br>"+value.type_name+"</td><td><a href=\"<?=SITE_URL?>admin/wl_users/"+value.email+"\" target='_blank'>"+value.email+'</a><br>'+value.user_phone+"</td><td><button onclick='setUser("+value.id+", "+value.type_id+")' class='btn btn-xs btn-success'><i class='fa fa-check'></i></button></td></tr>");
						})
					} else {
						$("#userTable").hide();
						$("#userError").show().css('color','#D12E39');
					}
				}
			})
		}
	}

	function setUser(id,type) {
		$('#userId').val(id);
		$('#userType').val(type);
		$('#wizard').bwizard('next');

		$('#modal-add-virtual-product form input[name=user_id]').val(id);
	}

	function saveNewUser() {
		$("#newUserError").hide();
		var name = $("#userName").val(),
			email = $("#userEmail").val(),
			phone = $("#userPhone").val()

		if(name && (email || phone))
		{
			$.ajax({
				url: "<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/saveNewUser'?>",
				type:"POST",
				data:{
					"name":name,
					"email":email,
					"phone":phone
				},
				success:function (res) {
					if(res['result'] == true){
						setUser(res['id'], <?= $_SESSION['option']->new_user_type?>);
					} else {
						$("#newUserError").show().text(res['message']).css('color','#d12e39');
					}
				}
			})
		} else
		{
			$("#newUserError").show().text("Введіть ім'я, емейл або телефон").css('color','#d12e39');
		}
	}

</script>