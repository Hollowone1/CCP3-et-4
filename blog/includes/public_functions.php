<?php 


function getPublishedPosts() {
	// use global $conn object in function
	global $conn;
	$sql = "SELECT * FROM posts WHERE published=true";
	$result = $conn->prepare($sql);
    $result->execute(array($sql));
    $posts = $result->fetchAll();

    $final_posts = array();
	foreach ($posts as $post) {
		$post['topic'] = getPostTopic($post['id']); 
		array_push($final_posts, $post);
	}

	return $final_posts;
}


function getPostTopic($post_id){
	global $conn;
	$sql = "SELECT * FROM topics WHERE id=
			(SELECT topic_id FROM post_topic WHERE post_id=:topic_token) LIMIT 1";
	$result = $conn->prepare($sql);
    $result->execute(array(":topic_token"=>$post_id));
	$topic = $result->fetchAll();
	return $topic;
}

function getPublishedPostsByTopic($topic_id) {
	global $conn;
	$sql = "SELECT * FROM posts ps 
			WHERE ps.id IN 
			(SELECT pt.post_id FROM post_topic pt 
				WHERE pt.topic_id=:topic_token GROUP BY pt.post_id 
				HAVING COUNT(1) = 1)";
	$result = $conn->prepare($sql);
    $result->execute(array("topic_token"=>$topic_id));
	$posts = $result->fetchAll();

	$final_posts = array();
	foreach ($posts as $post) {
		$post['topic'] = getPostTopic($post['id']); 
		array_push($final_posts, $post);
	}
	return $final_posts;
}

function getTopicNameById($id)
{
	global $conn;
	$sql = "SELECT name FROM topics WHERE id=:topic_token";
	$result = $conn->prepare($sql);
    $result->execute(array("topic_token"=>$id));
	$topic = $result->fetch(PDO::FETCH_ASSOC);
	return $topic['name'];
}

function getPost($slug){
	global $conn;
	$sql = "SELECT * FROM posts WHERE slug = :topic_token AND published = 1";
	$result = $conn->prepare($sql);
	$result->execute(array("topic_token"=>$slug));
	$post = $result->fetch(PDO::FETCH_ASSOC);
	if ($post) {
		$post['topic'] = getPostTopic($post['id']);
	}
	return $post;
}

function getAllTopics()
{
	global $conn;
	$sql = "SELECT * FROM topics";
	$result = $conn->prepare($sql);
	$result->execute();
	$topics = $result->fetchAll(PDO::FETCH_ASSOC);
	return $topics;
}
?>