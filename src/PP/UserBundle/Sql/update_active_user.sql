TRUNCATE TABLE active_user;
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