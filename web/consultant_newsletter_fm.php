<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
<title>Simple File Manager</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<style type="text/css">
ul {
  list-style: none;
  margin: 0 0 20px 0;
}
ul li {
  float: left;
  margin: 10px;
}
</style>
<body>
<div id="container">
    <h1>Vælg billede</h1>
    <ul>
<?php foreach (glob('images/nyhedsbrev/konsulent/*') as $image) {
        echo '<li><a href="http://'. $_SERVER['SERVER_NAME'] .'/'.$image.'" class="image"><img src="http://'. $_SERVER['SERVER_NAME'] .'/'.$image.'" style="width: 200px" /></a></li>';
} ?>
    </ul>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
<script src="/fx/scripts/tinymce/jscripts/tiny_mce/tiny_mce_popup.js"></script>

<script type="text/javascript">
  $(document).ready(function(){
    $(".image").click( function(e) {
      e.preventDefault();
      var url = $(this).attr('href');

      if ((url != '') && (typeof(url) != 'undefined')) {
        var win = tinyMCEPopup.getWindowArg("window");
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = url;
        //for image browsers
        try { win.ImageDialog.showPreviewImage(url); }
        catch (e) { void(e); }
        tinyMCEPopup.close();
      }
    });
  });
</script>

</body>
</html>
