<!doctype html>
<html amp lang="uk">
<head>
    <meta charset="utf-8">
    <title><?=html_entity_decode($_SESSION['alias']->title, ENT_QUOTES)?></title>
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <link rel="canonical" href="<?=SITE_URL?>"/>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Dosis" rel="stylesheet">
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    <style amp-custom>
        *                         { box-sizing: border-box; }
        a                         { text-decoration: none; }
        h1                        { font-size: 32px; margin: 0; text-align: center; letter-spacing: 2px;
                                    text-transform: uppercase; font-weight: 300; }
        h3                        { margin-top: 0; font-size: 18px; letter-spacing: 3px; text-transform: uppercase; }
        h3 a                      { color: #fbdf0f; text-decoration: none; }
        h3 a:hover                { text-decoration: underline; }
        body                      { font: 14px "Open Sans", Arial, Helvetica sans-serif; }
        h1,h2,h3,h4,h5,h6         { font-family: 'Dosis',Arial, Helvetica, sans-serif; font-weight: 400; }
        h1,h2,h3,h4,h5            { color: #eceff3; }

        .p55                      { padding: 55px 0; }
        .graybg                   { background-color: rgb(160,154,134); }

        .header                   { background: #000000; padding: 30px; }
        .nav-trigger              { margin-top: 2px; float: right; border: none; background: none; }
        .nav-trigger div          { width: 26px; height: 4px; background-color: #fbdf0f; margin-bottom: 4px;
                                    border-radius: 1px; }
        #subheader--library       { background-image: url('<?=IMG_PATH?>main/0/WARENDORF-159.jpg'); }
        #subheader,
        #subheader--library       { padding: 90px 0 70px; }

        amp-sidebar               { background: rgba(0,0,0,.9); text-align: right; }
        .prime-nav                { padding: 0 30px; }
        .prime-nav a              { color: white; text-transform: uppercase; text-decoration: none; letter-spacing: 3px;
                                    font-size: 11px; }
        .prime-nav a.active       { color: #fbdf0f; }
        .prime-nav li             { padding: 20px 0; border-bottom: 1px solid #333; list-style: none; }
        .prime-nav .close         { color: #fbdf0f; background: none; border: none; font-size: 44px; padding: 0;
                                    line-height: 50px; margin: 0 6px 24px 0; }

        .color                    { color: rgb(251,224,14); }

        #contact                  { background-color: rgb(78,78,77); padding: 30px 45px; }
        address                   { margin-top: 20px; line-height: 1.42857143; font-style: normal; color: white;
                                    font-size: 13px; margin-bottom: 60px; }
        address:last-of-type      { margin-bottom: 0; }
        address>a                 { color: white; text-decoration: none; display: block;  }
        address>a:hover           { text-decoration: underline; }
        address>h3,
        address>a:first-of-type   { border-bottom: 1px solid #222; padding-bottom: 5px; margin-bottom: 5px; }
        address>h3:last-of-type   { border-bottom: none; }

        .subfooter                { background: #0b0b0b; padding: 30px 30px 50px; text-align: center; color: white;
                                    font-size: 13px; }
        .subfooter a              { color: white; text-decoration: none; display: block; margin-top: 10px; }
        .subfooter a amp-img      { vertical-align: middle; }

        .btn-big                  { font-size: 14px; color: #eceff3; letter-spacing: 1px; line-height: normal;
                                    font-weight: bold; text-transform: uppercase; border: solid 1px #fff;
                                    padding: 10px 30px 10px 30px; }
        a.btn-line-black          { border: solid 1px rgb(78,78,77); color: #111; font-weight: normal; }
        a.btn-line-black:hover    { background: rgb(78,78,77); color: #fff; }
        .btn-line                 { color: #eceff3; border: solid 1px rgba(255,255,255,.2); padding: 3px 30px;
                                    text-transform: uppercase; display: inline-block; text-align: center;
                                    letter-spacing: 2px; }
        .btn-line:hover           { background: #fff; color: #111; }
        a.btn-line                { text-decoration: none; }
        .btn-fullwidth            { width: 100%; }

        .separator                { margin: 30px auto; width: 6px; height: 6px; background-color: #fbdf0f;
                                    border-radius: 50%; position: relative; }
        .separator:after,
        .separator:before         { content: ""; position: absolute; border-bottom: 1px solid rgba(255,255,255,.1);
                                    top: 2px; width: 300px; }
        .separator:after          { left: 100%; margin-left: 15px; }
        .separator:before         { right: 100%; margin-right: 15px; }

        #banner                   { padding: 100px 15px 0 15px; height: 480px; background-size: cover;
                                    background: url(<?=SITE_URL?>images/main/0/WARENDORF-302.jpg) no-repeat;
                                    text-shadow: rgb(0, 0, 0) 2px 4px 4px, rgb(0, 0, 0) 1px 2px 2px; }
        #banner a                 { line-height: 12px; text-decoration: none; text-transform: uppercase; color: white;
                                    border: 2px solid #fff; padding: 8px 23px; letter-spacing: 2px; font-weight: 700;
                                    font-size: 10px; }
        #banner a:hover           { color: #222; background-color: white; }
        #banner h2                { font-size: 33px; margin-bottom: 30px; padding: 0; letter-spacing: 7px; color: white;
                                    text-transform: uppercase; font-weight: 300; }
        #banner .cities           { font-size: 15px; line-height: 20px; font-weight: 400; color: rgb(255, 255, 255);
                                    letter-spacing: 4px; }

        #team,
        #standorte                { background-color: rgb(78,78,77); padding: 90px 0; }
        .masonry                  { width: 232px; margin: 50px auto 0; color: rgb(221, 221, 221); line-height: 24px;
                                    font-size: 13px; }
        .masonry a                { color: white; text-decoration: none; }
        .masonry p a:hover        { text-decoration: underline; }
        .masonry p                { margin: 0 0 30px; }

        #filters                  { font-size: 12px; padding: 0; margin: 60px 0 20px; font-weight: 400;
                                    text-align: center; }
        #filters li               { display: inline-block; margin-right: 5px; margin-bottom: 30px; min-width: 100px; }
        #filters li a             { padding: 10px 20px 8px 20px; color: rgb(78,78,77); font-size: 11px; letter-spacing: 3px;
                                    text-decoration: none; text-transform: uppercase; }
        #filters li a:hover       { color: rgb(251,224,14); background: rgb(78,78,77); }

        #inspiration              { background-color: rgb(160,154,134); padding-top: 50px; }
        .picframe                 { position: relative; }
        #gallery .item a          { display: none; color: #eceff3; text-transform: uppercase; letter-spacing: 2px;
                                    position: absolute; top: 0; left: 0; right: 0; bottom: 0; text-align: center;
                                    background-color: rgba(17,17,17,.8); padding-top: 30%; font-size: 13px; }
        #gallery .item:hover a    { display: block; }

        .bg-yellow                { background-color: rgb(251,224,14); padding: 55px 0; text-align: center; }

        #services                 { background-color: #191a1c; padding: 90px 30px; }
        .services__mask           { padding: 40px; background-color: rgba(0,0,0,.6); }
        .services__mask h3        { text-align: center; }
        .services__mask div       { color: white; }
        .services__block--1       { background: url("<?=SITE_URL?>style/images/110149.jpg") no-repeat; }
        .services__block--2       { background: url("<?=SITE_URL?>style/images/cook2_09_cmyk_HR.jpg") no-repeat; }
        .services__block--3       { background: url("<?=SITE_URL?>style/images/MK_4_094.jpg") no-repeat; }
        .services__block          { background-size: cover; margin-top: 20px; }

        #accessories              { background-color: rgb(78,78,77); padding-top: 50px; }
        #accessories--city        { background-color: rgb(160,154,134); padding-top: 50px; }

        #about                    { background: url(<?=SITE_URL?>assets/uploads/2015/07/bg-3.jpg) no-repeat;
                                    padding: 90px 0; background-size: cover; }
        #about h1                 { color: #fab702; }
        #about blockquote         { padding: 30px; background: rgba(0,0,0,0.5); color: #eceff3; }

        .partners                 { background: #222; padding: 60px 20px; }
        .partners ul              { padding: 0; }
        .partners li              { list-style: none; text-align: center; }

        .member                   { width: 283px; position: relative; margin: auto; }
        .member amp-img           { margin-bottom: -5px; }
        .team-desc                { display: none; position: absolute; left: 0; top: 0; right: 0; bottom: 0;
                                    padding: 40px; background-color: rgba(0,0,0,.9); color: white; }
        .team-desc .lead          { margin-bottom: 50px; position: relative; }
        .team-desc .lead:after    { content: ''; position: absolute; width: 50%; height: 1px; background-color: #ddd;
                                    left: 0; bottom: -15px; }
        .member:hover .team-desc  { display: block; }

        .project-view figure      { margin: 0; }

        @media(min-width: 400px)  {
            .partners li          { display: block; width: 150px; margin: 0 15px; }
            .partners ul          { display: flex; flex-wrap: wrap; justify-content: space-around; }
        }

        @media (min-width: 470px) {
            #banner h2            { font-size: 55px; }
        }
        @media (min-width: 480px) {
            #gallery              { overflow: hidden; }
            #gallery .item        { width: 50%; float: left; }
        }
        @media (min-width: 550px) {
            #standorte .wrap      { overflow: hidden; padding-left: calc(50% - 255px); }
            .masonry              { float: left; margin: 50px 15px 0; }
        }
        @media (min-width: 566px) {
            .team-wrap            { overflow: hidden; width: 566px; margin: auto; }
            .member               { float: left; margin: 0; }
        }
    </style>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
</head>
<body>
<?php
    include "@commons/header.php";
    if(isset($view_file)) require_once($view_file.'.php');
    include "@commons/footer.php";
?>
</body>
</html>