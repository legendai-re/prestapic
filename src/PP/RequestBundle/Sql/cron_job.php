<?php
$err_level = error_reporting(0);  
$mysqli = new mysqli("localhost", "root", "bubule28", "symfony");

$result = $mysqli->query("TRUNCATE TABLE active_user;");
$result = $mysqli->query("
	INSERT INTO active_user (user_id, contributionNb, createdDate)
	SELECT u0_.id AS id_0, COUNT(i1_.id) + COUNT(p2_.id) AS sclr_1 , CURRENT_TIMESTAMP() as currentDate
	FROM user u0_ 
	LEFT JOIN image_request i1_ ON u0_.id = i1_.author_id 
	LEFT JOIN proposition p2_ ON u0_.id = p2_.author_id 
	WHERE 
		u0_.enabled = 1 
		AND (
			i1_.createdDate BETWEEN SUBTIME(CURRENT_TIMESTAMP(), '7 00:00:00.000000') AND CURRENT_TIMESTAMP() 
			OR p2_.createdDate BETWEEN SUBTIME(CURRENT_TIMESTAMP(), '7 00:00:00.000000') AND CURRENT_TIMESTAMP()
		) 
	GROUP BY u0_.id 
	ORDER BY sclr_1 DESC 
	LIMIT 3;
");

$result = $mysqli->query("TRUNCATE TABLE popular_request;");
$result = $mysqli->query("	
	INSERT INTO popular_request (image_request_id, createdDate)
	SELECT DISTINCT i0_.id AS id_0, CURRENT_TIMESTAMP() as currentDate
	FROM image_request i0_ 
	LEFT JOIN user u1_ ON i0_.author_id = u1_.id 
	WHERE (i0_.createdDate BETWEEN SUBTIME(CURRENT_TIMESTAMP(), '7 00:00:00.000000') AND CURRENT_TIMESTAMP()) AND u1_.enabled = 1 AND i0_.enabled = 1 
	ORDER BY i0_.upvote + i0_.propositions_nb 
	DESC LIMIT 3;
");

$mysqli->close();