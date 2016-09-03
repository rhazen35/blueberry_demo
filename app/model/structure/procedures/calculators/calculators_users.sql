CREATE PROCEDURE
`proc_getUserExcelHash`(
  IN userId INT(11)
)
  BEGIN
    SELECT hash FROM calculators_user WHERE user_id = userId;
  END $$

CREATE PROCEDURE
`proc_newUserExcelHash`(
 IN excelId INT(11),
 IN userId INT(11),
 IN userHash VARCHAR(256),
 IN excelDate DATE,
 IN excelTime TIME
)
  BEGIN
    INSERT INTO calculators_user VALUES(excelId, userId, userHash, excelDate, excelTime);
  END $$