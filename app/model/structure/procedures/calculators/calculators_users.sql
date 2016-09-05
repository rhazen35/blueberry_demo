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
 IN userExt VARCHAR(10),
 IN excelDate DATE,
 IN excelTime TIME
)
  BEGIN
    INSERT INTO calculators_user VALUES(excelId, userId, userHash, userExt, excelDate, excelTime);
  END $$

CREATE PROCEDURE
`proc_getUserExcel`(
  IN userId INT(11)
)
  BEGIN
    SELECT id, hash, ext, date, time FROM calculators_user WHERE user_id = userId;
  END $$