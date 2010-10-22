<?php
	$html = getLoginPage();
	$p = adPage::getInstance();
	$p->setTitle("login");
	$p->setBreadcrumb(array("login"));
	$p->addCss("adCss/home/login_admin-screen.css");
	$p->addOnload('document.getElementById("username").focus()');
	$p->addContent($html);
	$p->printPage();
	
///////////////////////////////////////////////////////////////////////

function getLoginPage(){
	$username = isset($_POST['login_admin']['username']) ? $_POST['login_admin']['username'] : "";
	$error = isset($GLOBALS['LOGIN_ERROR']) ? $GLOBALS['LOGIN_ERROR'] : "";
	$adminPath = ADMIN_PATH;
	$html = <<<HTML
	<div class='fullWidth'>
		<form class='generic' action='/{$adminPath}/{$GLOBALS['NAV_PATH']['url']}' method='post'>
			<fieldset>
				<legend>Login</legend>
				<label for='username'>Username</label>
				<input type='text' class='text' name='login_admin[username]' value='{$username}' id='username' />
				
				<label for='password'>Password</label>
				<input type='password' class='text' name='login_admin[password]' id='password' />
				
				<label for='login'>Login to your account</label>
				<input type='submit' id='login' value='login' class='submit'/>
			</fieldset>
		</form>
		<p class='alert'>{$error}</p>
	</div>
HTML;
	
	return $html;
}
?>