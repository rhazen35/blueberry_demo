CREATE PROCEDURE
`proc_getCalculatorIdByProjectId`(
  IN projectId INT(11)
)
  BEGIN
    SELECT calculator_id FROM projects_calculators WHERE project_id = projectId;
  END $$

CREATE PROCEDURE
  `proc_getMatchingCalculatorHash`(
  IN fileHash VARCHAR(256)
)
  BEGIN
    SELECT hash FROM calculators WHERE hash = fileHash;
  END $$

CREATE PROCEDURE
  `proc_newCalculator`(
  IN `calculatorId` INT(11),
  IN `userId` INT(11),
  IN `calculatorHash` VARCHAR(256),
  IN `calculatorDate` DATE,
  IN `calculatorTime` TIME,
  OUT `InsertId` INT(11)
)
  BEGIN
    INSERT INTO calculators (id, user_id, hash, date, time)
    VALUES(calculatorId, userId, calculatorHash, calculatorDate, calculatorTime);
    SET InsertId = last_insert_id();
    SELECT InsertId;
  END $$

CREATE PROCEDURE
  `proc_getCalculator`(
  IN calculatorId INT(11)
)
  BEGIN
    SELECT user_id, hash, date, time FROM calculators WHERE id = calculatorId;
  END $$

CREATE PROCEDURE
  `proc_getCalculatorIdByHash`(
  IN calculatorHash VARCHAR(256)
)
  BEGIN
    SELECT id FROM calculators WHERE hash = calculatorHash;
  END $$

CREATE PROCEDURE
  `proc_getAllCalculatorsByUser`(
  IN userId INT(11)
)
  BEGIN
    SELECT id, hash, date, time FROM calculators WHERE user_id = userId;
  END $$
