<!-- begin #header -->
<div id="header" class="header navbar navbar-default navbar-fixed-top">
  <!-- begin container-fluid -->
  <div class="container-fluid">
    <!-- begin mobile sidebar expand / collapse button -->
    <div class="navbar-header">
      <a href="<?=SITE_URL?>" class="navbar-brand"><span class="navbar-logo"></span> <?=SITE_NAME?></a>
      <button type="button" class="navbar-toggle" data-click="sidebar-toggled">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>
    <!-- end mobile sidebar expand / collapse button -->
    
    <!-- begin header navigation right -->
    <ul class="nav navbar-nav navbar-right">
      <li>
        <form action="<?=SITE_URL?>admin/search" class="navbar-form full-width">
          <div class="form-group">
            <input name="by" type="text" class="form-control" placeholder="Ключове слово пошуку..." />
            <button type="submit" class="btn btn-search"><i class="fa fa-search"></i></button>
          </div>
        </form>
      </li>
      <li class="dropdown navbar-user">
        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
          <img src="<?=SITE_URL?>style/admin/images/user-<?=$_SESSION['user']->type?>.jpg" alt="" /> 
          <span class="hidden-xs"><?=$_SESSION['user']->name?></span> <b class="caret"></b>
        </a>
        <ul class="dropdown-menu animated fadeInLeft">
          <li class="arrow"></li>
          <li><a href="<?=SITE_URL?>admin/wl_users/my">Редагувати профіль</a></li>
          <li class="divider"></li>
          <li><a href="<?=SITE_URL?>logout">Вийти</a></li>
        </ul>
      </li>
    </ul>
    <!-- end header navigation right -->
  </div>
  <!-- end container-fluid -->
</div>
<!-- end #header -->