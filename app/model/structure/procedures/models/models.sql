CREATE PROCEDURE
  `proc_getMatchingModelHash`(
  IN fileHash VARCHAR(256)
)
  BEGIN
    SELECT hash FROM xml_models WHERE hash = fileHash;
  END $$

CREATE PROCEDURE
  `proc_newModel`(
  IN `modelId` INT(11),
  IN `userId` INT(11),
  IN `modelName` VARCHAR(100),
  IN `modelHash` VARCHAR(256),
  IN `modelValid` VARCHAR(3),
  IN `modelDate` DATE,
  IN `modelTime` TIME,
  OUT `InsertId` INT(11)
)
  BEGIN
    INSERT INTO xml_models (id, user_id, name, hash, valid, date, time)
    VALUES(modelId, userId, modelName, modelHash, modelValid, modelDate, modelTime);
    SET InsertId = last_insert_id();
    SELECT InsertId;
  END $$

CREATE PROCEDURE
  `proc_getModel`(
  IN modelId INT(11)
)
  BEGIN
    SELECT user_id, name, hash, valid, date, time FROM xml_models WHERE id = modelId;
  END $$

CREATE PROCEDURE
`proc_getModelIdByHash`(
  IN modelHash VARCHAR(256)
)
  BEGIN
    SELECT id FROM xml_models WHERE hash = modelHash;
  END $$

CREATE PROCEDURE
`proc_getAllModelsByUser`(
  IN userId INT(11)
)
  BEGIN
    SELECT id, hash, name, valid, date, time FROM xml_models WHERE user_id = userId;
  END $$
