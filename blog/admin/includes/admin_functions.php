<?php 
// Admin user variables
$admin_id = 0;
$isEditingUser = false;
$username = "";
$role = "";
$email = "";
// general variables
$errors = [];

/* - - - - - - - - - - 
-  Admin users actions
- - - - - - - - - - -*/
// if user clicks the create admin button
if (isset($_POST['create_admin'])) {
	createAdmin($_POST);
}
// if user clicks the Edit admin button
if (isset($_GET['edit-admin'])) {
	$isEditingUser = true;
	$admin_id = $_GET['edit-admin'];
	editAdmin($admin_id);
}
// if user clicks the update admin button
if (isset($_POST['update_admin'])) {
	updateAdmin($_POST);
}
// if user clicks the Delete admin button
if (isset($_GET['delete-admin'])) {
	$admin_id = $_GET['delete-admin'];
	deleteAdmin($admin_id);
}

/* - - - - - - - - - - - -
-  Admin users functions
- - - - - - - - - - - - -*/
/* * * * * * * * * * * * * * * * * * * * * * *
* - Receives new admin data from form
* - Create new admin user
* - Returns all admin users with their roles 
* * * * * * * * * * * * * * * * * * * * * * */
function createAdmin($request_values){
	global $conn, $errors, $role, $username, $email;
	$username = htmlspecialchars($request_values['username']);
	$email = htmlspecialchars($request_values['email']);
	$password = htmlspecialchars($request_values['password']);
	$passwordConfirmation = htmlspecialchars($request_values['passwordConfirmation']);

	if(isset($request_values['role'])){
		$role = htmlspecialchars($request_values['role']);
	}
	// form validation: ensure that the form is correctly filled
	if (empty($username)) { array_push($errors, "Uhmm...We gonna need the username"); }
	if (empty($email)) { array_push($errors, "Oops.. Email is missing"); }
	if (empty($role)) { array_push($errors, "Role is required for admin users");}
	if (empty($password)) { array_push($errors, "uh-oh you forgot the password"); }
	if ($password != $passwordConfirmation) { array_push($errors, "The two passwords do not match"); }
	// Ensure that no user is registered twice. 
	// the email and usernames should be unique
	$user_check_query = "SELECT * FROM users WHERE username='username = :topic' 
							OR email='email= :topic2' LIMIT 1";
    $result= $conn -> prepare($user_check_query);
    $result->execute(array("topic"=>$username));
    $result->execute(array("topic2"=>$email));
    $user = $result->fetch(PDO::FETCH_ASSOC);
	if ($user) { // if user exists
		if ($user['username'] === $username) {
		  array_push($errors, "Username already exists");
		}

		if ($user['email'] === $email) {
		  array_push($errors, "Email already exists");
		}
	}
	// register user if there are no errors in the form
	if (count($errors) == 0) {
		$password = password_hash($password);//encrypt the password before saving in the database
		$query = "INSERT INTO users (username, email, role, password, created_at, updated_at) 
				  VALUES('$username', '$email', '$role', '$password', now(), now())";
		$result= $conn->prepare($query);
        $result->execute();
        $call= $result->fetchAll(PDO::FETCH_ASSOC);

		$_SESSION['message'] = "Admin user created successfully";
		header('location: users.php');
		exit(0);
	}
}
/* * * * * * * * * * * * * * * * * * * * *
* - Takes admin id as parameter
* - Fetches the admin from database
* - sets admin fields on form for editing
* * * * * * * * * * * * * * * * * * * * * */
function editAdmin($admin_id)
{
	global $conn, $username, $role, $isEditingUser, $admin_id, $email;

	$sql = "SELECT * FROM users WHERE id='admin_id= :topic' LIMIT 1";
    $result= $conn->prepare($sql);
    $result->execute(array("topic"=>$admin_id));
    $admin=$result->fetch(PDO::FETCH_ASSOC);

	// set form values ($username and $email) on the form to be updated
	$username = $admin['username'];
	$email = $admin['email'];
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
* - Receives admin request from form and updates in database
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function updateAdmin($request_values){
	global $conn, $errors, $role, $username, $isEditingUser, $admin_id, $email;
	// get id of the admin to be updated
	$admin_id = $request_values['admin_id'];
	// set edit state to false
	$isEditingUser = false;


	$username = htmlspecialchars($request_values['username']);
	$email = htmlspecialchars($request_values['email']);
	$password = htmlspecialchars($request_values['password']);
	$passwordConfirmation = htmlspecialchars($request_values['passwordConfirmation']);
	if(isset($request_values['role'])){
		$role = $request_values['role'];
	}
	// register user if there are no errors in the form
	if (count($errors) == 0) {
		//encrypt the password (security purposes)
		$password = password_hash($password);

		$query = "UPDATE users SET username='$username', email='$email', role='$role', password='$password' WHERE id=$admin_id";
		$result = $conn->prepare($query);
        $result->execute();
        $form= $result->FetchAll();

		$_SESSION['message'] = "Admin user updated successfully";
		header('location: users.php');
		exit(0);
	}
}
// delete admin user 
function deleteAdmin($admin_id) {
	global $conn;
	$sql = "DELETE FROM users WHERE id='admin_id=:topic'";
    $result= $conn->prepare($sql);
    $result->execute(array("topic"=>$admin_id));
    $message= $result->fetch();
	if (!empty ($message)) {
		$_SESSION['message'] = "User successfully deleted";
		header("location: users.php");
		exit(0);
	}
}


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
* - Returns all admin users and their corresponding roles
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function getAdminUsers(){
	global $conn, $roles;
	$sql = "SELECT * FROM users WHERE role IS NOT NULL";
    $result= $conn->prepare($sql);
    $result->execute();
    $users= $result->fetchAll(PDO::FETCH_ASSOC);

	return $users;
}
/* * * * * * * * * * * * * * * * * * * * *
* - Escapes form submitted value, hence, preventing SQL injection
* * * * * * * * * * * * * * * * * * * * * */
function esc(String $value){
	// bring the global db connect object into function
	global $conn;
	// remove empty space sorrounding string
	$val = trim($value); 
	$val = mysqli_real_escape_string($conn, $value);
	return $val;
}
// Receives a string like 'Some Sample String'
// and returns 'some-sample-string'
function makeSlug(String $string){
	$string = strtolower($string);
	$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	return $slug;
}

// Admin user variables
// ... varaibles here ...

// Topics variables
$topic_id = 0;
$isEditingTopic = false;
$topic_name = "";

/* - - - - - - - - - - 
-  Admin users actions
- - - - - - - - - - -*/
// ... 

/* - - - - - - - - - - 
-  Topic actions
- - - - - - - - - - -*/
// if user clicks the create topic button
if (isset($_POST['create_topic'])) { createTopic($_POST); }
// if user clicks the Edit topic button
if (isset($_GET['edit-topic'])) {
	$isEditingTopic = true;
	$topic_id = $_GET['edit-topic'];
	editTopic($topic_id);
}
// if user clicks the update topic button
if (isset($_POST['update_topic'])) {
	updateTopic($_POST);
}
// if user clicks the Delete topic button
if (isset($_GET['delete-topic'])) {
	$topic_id = $_GET['delete-topic'];
	deleteTopic($topic_id);
}


/* - - - - - - - - - - - -
-  Admin users functions
- - - - - - - - - - - - -*/
// ...

/* - - - - - - - - - - 
-  Topics functions
- - - - - - - - - - -*/
// get all topics from DB
function getAllTopics() {
	global $conn;
	$sql = "SELECT * FROM topics";
    $result = $conn->prepare($sql);
    $result->execute();
    $topics = $result->fetchAll(PDO::FETCH_ASSOC);
	return $topics;
}
function createTopic($request_values){
	global $conn, $errors, $topic_name;
	$topic_name = htmlspecialchars($request_values['topic_name']);
	// create slug: if topic is "Life Advice", return "life-advice" as slug
	$topic_slug = makeSlug($topic_name);
	// validate form
	if (empty($topic_name)) { 
		array_push($errors, "Topic name required"); 
	}
	// Ensure that no topic is saved twice. 
	$topic_check_query = "SELECT * FROM topics WHERE slug='topic_slug=:token' LIMIT 1";
    $result = $conn->prepare($topic_check_query);
    $result->execute(array("token"=>$topic_slug));
    $topic= $result->fetch(PDO::FETCH_ASSOC);

	if ($topic = $result->rowCount() > 0) { // if topic exists
		array_push($errors, "Topic already exists");
	}
	// register topic if there are no errors in the form
	if (count($errors) == 0) {
		$query = "INSERT INTO topics (name, slug) 
				  VALUES('topic_name = :token', 'topic_slug=:token2')";
        $result= $conn->prepare($query);
        $result->execute(array("token"=>$topic_name));
        $result->execute(array("token2"=>$topic_slug));
        $register_error= $result->fetch(PDO::FETCH_ASSOC);

		$_SESSION['message'] = "Topic created successfully";
		header('location: topics.php');
		exit(0);
	}
}
/* * * * * * * * * * * * * * * * * * * * *
* - Takes topic id as parameter
* - Fetches the topic from database
* - sets topic fields on form for editing
* * * * * * * * * * * * * * * * * * * * * */
function editTopic($topic_id) {
	global $conn, $topic_name, $isEditingTopic, $topic_id;
	$sql = "SELECT * FROM topics WHERE id='topic_id=:token' LIMIT 1";
    $result= $conn->prepare($sql);
    $result->execute(array("token"=>$topic_id));
    $topic= $result->fetch(PDO::FETCH_ASSOC);
	// set form values ($topic_name) on the form to be updated
	$topic_name = $topic['name'];
}
function updateTopic($request_values) {
	global $conn, $errors, $topic_name, $topic_id;
	$topic_name = htmlspecialchars($request_values['topic_name']);
	$topic_id = htmlspecialchars($request_values['topic_id']);
	// create slug: if topic is "Life Advice", return "life-advice" as slug
	$topic_slug = makeSlug($topic_name);
	// validate form
	if (empty($topic_name)) { 
		array_push($errors, "Topic name required"); 
	}
	// register topic if there are no errors in the form
	if (count($errors) == 0) {
		$query = "UPDATE topics SET name='topic_name=:token', slug='topic_slug=:token2' WHERE id='topic_id=:token3'";
		$result= $conn->prepare($query);
        $result->execute(array("token"=>$topic_name));
        $result->execute(array("token2"=>$topic_slug));
        $result->execute(array("token3"=>$topic_id));
        $topic = $result->fetch(PDO::FETCH_ASSOC);

		$_SESSION['message'] = "Topic updated successfully";
		header('location: topics.php');
		exit(0);
	}
}
// delete topic 
function deleteTopic($topic_id) {
	global $conn;
	$sql = "DELETE FROM topics WHERE id='topic_id=:token";
    $result = $conn->prepare($sql);
    $result->execute(array("token"=>$topic_id));
    $delete = $result->fetch(PDO::FETCH_ASSOC); 
	if (!empty($delete)) {
		$_SESSION['message'] = "Topic successfully deleted";
		header("location: topics.php");
		exit(0);
	}
}
?>