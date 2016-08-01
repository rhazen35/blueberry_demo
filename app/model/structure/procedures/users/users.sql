CREATE PROCEDURE
`proc_newUser`(
    INOUT id INT(11),
    INOUT email VARCHAR(150),
    INOUT email_code VARCHAR(256),
    INOUT password VARCHAR(256),
    INOUT type VARCHAR(25),
    INOUT timestamp DATETIME
)
  BEGIN
      INSERT INTO users VALUES(id, email, email_code, password, type, timestamp);
  END $$

CREATE PROCEDURE
`proc_checkLoginExists`(
  IN userId INT(11)
)
  BEGIN
    SELECT id FROM user_login WHERE user_id = userId;
  END $$

CREATE PROCEDURE
`proc_newLogin`(
  IN id INT(11),
  IN user_id INT(11),
  IN previous DATETIME,
  IN current DATETIME,
  IN first DATETIME,
  IN count INT(11)
)
  BEGIN
    INSERT INTO user_login VALUES(id, user_id, previous, current, first, count);
  END $$

CREATE PROCEDURE
`proc_getLastLogin`(
  IN userId INT(11)
)
  BEGIN
    SELECT current FROM user_login WHERE user_id = userId;
  END $$

CREATE PROCEDURE
  `proc_getPreviousLogin`(
  IN userId INT(11)
)
  BEGIN
    SELECT previous FROM user_login WHERE user_id = userId;
  END $$

CREATE PROCEDURE
`proc_updateLogin`(
  IN userId INT(11),
  IN previousDate DATETIME,
  IN currentDate DATETIME
)
  BEGIN
    UPDATE user_login SET previous = previousDate, current = currentDate, count = count + 1 WHERE user_id = userId;
  END $$

CREATE PROCEDURE
`proc_getUserPass`(
  IN userEmail VARCHAR(125)
)
  BEGIN
    SELECT password FROM users WHERE email = userEmail;
  END $$

CREATE PROCEDURE
`proc_getUserEmailById`(
  IN userId INT(11)
)
  BEGIN
    SELECT email FROM users WHERE id = userId;
  END $$