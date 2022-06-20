<?php 

	$username2 = "";
	$email    = "";
	$errors = array(); 

	// REGISTER USER
	if (isset($_POST['reg_user'])) {

		// receive all input values from the form
		$username = htmlspecialchars($_POST['username']);
		$email = htmlspecialchars($_POST['email']);
		$password_1 = htmlspecialchars($_POST['password_1']);
		$password_2 = htmlspecialchars($_POST['password_2']);

		// form validation: ensure that the form is correctly filled
		if (empty($username)) {  array_push($errors, "User manquant"); }
		if (empty($email)) { array_push($errors, "E-mail manquant"); }
		if (empty($password_1)) { array_push($errors, "vous avez oublié le mot de passe"); }
		if ($password_1 != $password_2) { array_push($errors, "les deux mots de passe ne correspondent pas");}

		// Ensure that no user is registered twice. 
		// the email and usernames should be unique
		$user_check_query = "SELECT * FROM users WHERE username='$username' 
								OR email='$email' LIMIT 1";

		$result = $conn-> prepare($user_check_query);
		$result -> execute(array());
		$user= $result->fetch(PDO::FETCH_ASSOC);

		if ($user) { // if user exists
			if ($user['username'] === $username) {
			  array_push($errors, "User existe déjà");
			}
			if ($user['email'] === $email) {
			  array_push($errors, "Email existe déjà");
			}
		}
		// register user if there are no errors in the form
		if (count($errors) == 0) {
			$password = password_hash($password_1);//encrypt the password before saving in the database
			$query = "INSERT INTO users (username, email, password, created_at, updated_at) 
					  VALUES('$username', '$email', '$password', now(), now())";
			$result= $conn->prepare($query);
			$result->execute(array());

			// get id of created user
			$reg_user_id = $conn ->lastInsertId(); 

			// put logged in user into session array
			$_SESSION['user'] = getUserById($reg_user_id);

			// if user is admin, redirect to admin area
			if ( in_array($_SESSION['user']['role'], ["Admin", "Author"])) {
				$_SESSION['message'] = "Vous êtes maintenant connectés";
				// redirect to admin area
				header('location: ' . BASE_URL . 'admin/dashboard.php');
				exit(0);
			} else {
				$_SESSION['message'] = "Vous êtes maintenant connectés";
				// redirect to public area
				header('location: index.php');				
				exit(0);
			}
		}
	}

	// LOG USER IN
	if (isset($_POST['login_btn'])) {
		$username = htmlspecialchars($_POST['username']);
		$password = htmlspecialchars($_POST['password']);

		if (empty($username)) { array_push($errors, "Username required"); }
		if (empty($password)) { array_push($errors, "Password required"); }
		if (empty($errors)) {
			$password = password_hash('password', PASSWORD_BCRYPT); // encrypt password
			$sql = "SELECT * FROM users WHERE username='username:topic' and password='password:topic2' Limit = 1";
			$result= $conn->prepare($sql);
			$result->execute(array(":topic"=>$username));
			$result->execute(array(":topic2"=>$password));
			$reg_user_id = $result->fetch(PDO::FETCH_ASSOC);

			if ($count = $result->rowCount() > 0) {
				// get id of created user
				$reg_user_id = $result->fetch(PDO::FETCH_ASSOC)['id']; 

				// put logged in user into session array
				$_SESSION['user'] = getUserById($reg_user_id); 

				// if user is admin, redirect to admin area
				if ( in_array($_SESSION['user']['role'], ["Admin", "Author"])) {
					$_SESSION['message'] = "Vous êtes maintenant connectés";
					// redirect to admin area
					header('location: ' . BASE_URL . '/admin/dashboard.php');
					exit(0);
				} else {
					$_SESSION['message'] = "Vous êtes maintenant connectés";
					// redirect to public area
					header('location: index.php');				
					exit(0);
				}
			} else {
				array_push($errors, 'Mauvaise présentation gilbert');
			}
		}
	}
	// escape value from form
	function esc(String $value)
	{	
		// bring the global db connect object into function
		global $conn;

		$val = trim($value); // remove empty space sorrounding string
		$val = $conn->prepare($value);
		$val -> execute(array());

		return $val;
	}
	// Get user info from user id
	function getUserById($id)
	{
		global $conn;
		$sql = "SELECT * FROM users WHERE id= 'id:topic' LIMIT 1";

		$result = $conn->prepare($sql);
		$result-> execute(array(":topic"=>$id));
		$user= $result->fetch(PDO::FETCH_ASSOC);

		// returns user in an array format: 
		// ['id'=>1 'username' => 'Awa', 'email'=>'a@a.com', 'password'=> 'mypass']
		return $user; 
	}
?>