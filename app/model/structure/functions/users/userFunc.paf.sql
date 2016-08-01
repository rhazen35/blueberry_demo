CREATE FUNCTION
`f_checkEmailExists`(`input` VARCHAR(150))
RETURNS int(11)
BEGIN
    DECLARE output int(11);
    SELECT id INTO output FROM users WHERE email = input;
    RETURN output;
END$$

CREATE FUNCTION
`f_checkEmailPass`(`input` VARCHAR(150))
RETURNS varchar(256)
BEGIN
    DECLARE output varchar(256);
    SELECT password INTO output FROM users WHERE email = input;
    RETURN output;
END$$

CREATE FUNCTION
`f_getUserId`(`input` varchar(150))
RETURNS int(11)
BEGIN
    DECLARE output int(11);
    SELECT id INTO output FROM users WHERE email = input;
    RETURN output;
END$$

CREATE FUNCTION
`f_getUserType`(`input` int(11))
RETURNS varchar(25)
BEGIN
    DECLARE output varchar(25);
    SELECT type INTO output FROM users WHERE id = input;
    RETURN output;
END$$