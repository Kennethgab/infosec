<?php

namespace ttm4135\webapp\controllers;
use ttm4135\webapp\Auth;
use ttm4135\webapp\models\User;

class LoginController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {
            $this->render('login.twig', ['title'=>"Login"]);
        }
    }

    function login()
    {
	$request = $this->app->request;

	// Count login attempts
	if( isset( $_SESSION['login_counter'] ) ) {
      	    $_SESSION['login_counter'] += 1;
   	}else {
	    $_SESSION['login_counter'] = 1;
	    $_SESSION['last_login_try'] = time();
	}

	if ($_SESSION['login_counter'] > 5 && time() - $_SESSION['last_login_try'] < 10) {
	    $this->app->flashNow('error', 'Too many wrong attempts! You can try agein in ' . (string)(10 - (time() - $_SESSION['last_login_try']) . ' seconds.'));
	    $this->render('login.twig', []);
	}
	else {

	    $username = $request->post('username');
            $password = $request->post('password');

            if ( Auth::checkCredentials($username, $password) ) {
                $user = User::findByUser($username);
                $_SESSION['userid'] = $user->getId();
                $this->app->flash('info', "You are now successfully logged in as " . $user->getUsername() . ".");
                $this->app->redirect('/');
            } else {
                $this->app->flashNow('error', 'Incorrect username/password combination.');
		$_SESSION['last_login_try'] = time();

                if ($_SESSION['login_counter'] > 5){
		    $_SESSION['login_counter'] = 1;
		}

		$this->render('login.twig', []);
	    }
	
	}
    }

    function logout()
    {   
        Auth::logout();
        $this->app->flashNow('info', 'Logged out successfully!!');
        $this->render('base.twig', []);
        return;
       
    }
}
