<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Quest Alliance Login</title>
<?php
require('../../config.php');
?>
<script type="text/javascript">
/* Updated by Kalyani, for adding multiuser login  */
function showMe(id) { // This gets executed when the user clicks on the checkbox
    
    var obj = document.getElementById(id);

    if(document.getElementById("multi_user_login").checked) {        
        obj.style.display = "block";
    }else { 
        obj.style.display = "none";
    }
}

</script>
<style type="text/css">
body{
	font-family:"Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
}
#bg{
	top:0px;
	left:0px;
	width:100%;
	height:100%;
	display:block; /*!*/
	position:absolute;
	z-index:1;
}
p, h1, form, button{
	border:0;
	margin:0;
	padding:0;
}
.spacer{clear:both; height:1px;}
/* ----------- My Form ----------- */
.myform{
	margin:0 auto;
	width:400px;
	padding:14px;
	position: absolute;
	left: 88px;
	top: 67px;
	z-index:5;
}

/* ----------- stylized ----------- */
#stylized{
	border:7px solid #666;
	-moz-border-radius: 15px;
	border-radius: 15px;
	background-color: #ebf4fb;
}

#stylized h1 {
font-size:16px;
font-weight:bold;
margin-bottom:8px;
}
#stylized p{
font-size:11px;
color:#666666;
margin-bottom:20px;
border-bottom:solid 1px #b7ddf2;
padding-bottom:10px;
}
#stylized label{
display:block;
font-weight:bold;
text-align:right;
width:140px;
float:left;
}
#stylized .small{
color:#666666;
display:block;
font-size:11px;
font-weight:normal;
text-align:right;
width:140px;
}
#stylized input{
float:left;
font-size:12px;
padding:4px 2px;
border:solid 1px #aacfe4;
width:200px;
margin:2px 0 20px 10px;
}
#stylized button{
clear:both;
margin-left:150px;
width:150px;
height:35px;
background:url(button.png) no-repeat;
text-align:center;
line-height:31px;
color:#FFFFFF;
font-size:11px;
font-weight:bold;
}

#dps {
	position: absolute;
	z-index: 10;
	left: 315px;
	top: 90px;
	font-size: 16px;
	padding: 5px;
}
</style>
</head>

<body>
<img id="bg" src="bkg.jpg" />
<div id="dps">Welcome to Quest <br>Alliance Learning Portal!</div>

<div id="stylized" class="myform">
<img src="SigHP.JPG" alt="Quest Alliance" style="width: 100px;"/>

  <img src="QA Logo_New.jpg" alt="Quest Alliance" style="width: 100px;"/>
 <?php
echo '<form id="form" name="form" method="post" action="../index.php">';
?>
<p></p>

<label>UserName
<span class="small">Enter your Username</span>
</label>
<input type="text" name="username" id="name" />
<label>Password
<span class="small">Enter Your Password</span>
</label>
<input type="password" name="password" id="password" />
<div id="multi">
	<input type="checkbox" name="c1" id="multi_user_login" onclick="showMe('divmultiuser')" style="width: 21px;float: left;margin: 0px 0px 20px 150px;">
	<label for="rememberusername" style="width: 105px;padding-bottom: 20px;">Multiuser login</label>
</div>

<div class="clearer"><!-- --></div>
            <div id="divmultiuser" style="display:none;">
                <div class="form-label"><label for="username"><?php print_string("username") ?><span class="small">Enter your Username</span></label></div>
                <div class="form-input">

                  <input type="text" name="username2" id="username2" size="15" value="<?php p($frm->username2, true) ?>" />
                </div>
                <div class="clearer"><!-- --></div>
                <div class="form-label"><label for="password"><?php print_string("password") ?><span class="small">Enter Your Password</span></label></div>

                <div class="form-input">
                  <input type="password" name="password2" id="password2" size="15" value="" />

                </div>
               </div>
<div class="clearer"><!-- --></div>

<button type="submit">Login to Moodle</button>
<div class="spacer"></div>

</form>
</div>
</body>
</html>
