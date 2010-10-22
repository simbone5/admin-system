<?php
function checkLogin(){
	if(isset($_GET['logout'])){
		logout();
	}
	
	if(!isset($_SESSION['ADMIN_USER']) || $_SESSION['ADMIN_USER']==NULL){
		return checkLoginAttempt();
	}
	else{
		$GLOBALS['ADMIN_LOGGED_IN'] = TRUE;
		return TRUE;
	}
}

function checkLoginAttempt(){
	if(isset($_POST['login_admin'])){
		$login = $_POST['login_admin'];
		if(isset($login['username']) && trim($login['username'])!="" && isset($login['password']) && trim($login['password'])!=""){
			$user = new db_user();
			$user->openFromUsernameAndPassword(trim($login['username']), $login['password']);
			if($user->getId()>0){
				$_SESSION['ADMIN_USER'] = $user;
				$GLOBALS['ADMIN_LOGGED_IN'] = TRUE;
				return TRUE;
			}
			$GLOBALS['LOGIN_ERROR'] = "error - incorrect username and password";
		}
		else
			$GLOBALS['LOGIN_ERROR'] = "error - username and password required";
	}
	
	$GLOBALS['ADMIN_LOGGED_IN'] = FALSE;
	return FALSE;
}

function logout(){
	unset($_SESSION['ADMIN_USER']);
	$GLOBALS['ADMIN_LOGGED_IN'] = FALSE;
}

function checkSuperAdmin(){
	if(!$_SESSION['ADMIN_USER']->getSuperAdmin()){
		$adError = adError::getInstance();
		$adError->addUserError("Permission denied");
		$adError->printErrorPage();
	}
}
?>