TRUNCATE TABLE popular_request;
INSERT INTO popular_request (image_request_id, createdDate)
SELECT DISTINCT i0_.id AS id_0, CURRENT_TIMESTAMP() as currentDate
FROM image_request i0_ 
LEFT JOIN user u1_ ON i0_.author_id = u1_.id 
WHERE (i0_.createdDate BETWEEN SUBTIME(CURRENT_TIMESTAMP(), '7 00:00:00.000000') AND CURRENT_TIMESTAMP()) AND u1_.enabled = 1 AND i0_.enabled = 1 
ORDER BY i0_.upvote + i0_.propositions_nb 
DESC LIMIT 3;