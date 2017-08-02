





<!DOCTYPE html>
<html>
<base href="<?php echo U('frontend/index/index','',false);?>" />
  <head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <title>提示-快云触屏版</title>
    <link rel="stylesheet" href="__FRONTEND_MOBILE_CSS__base.css">
    <link rel="stylesheet" href="__FRONTEND_MOBILE_CSS__ky-type.css"></head>
   <body class="bc293038">
    <nav class="login-title">
       <a href="<?php echo($jumpUrl); ?>" class="return-icona"></a>温馨提示
    </nav>
    <section class="wrong-con">
        <div class="wrong-img"></div>
        <p class="tips-wrong"><?php echo($error); ?></p>
        <a href="<?php echo($jumpUrl); ?>" class="fresh-try"><i class="wrg-icon"></i>返回</a>
    </section>
  </body>
</html>


