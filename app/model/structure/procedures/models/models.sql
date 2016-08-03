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
  IN `modelHash` VARCHAR(256),
  IN `modelDate` DATE,
  IN `modelTime` TIME,
  OUT `InsertId` INT(11)
)
  BEGIN
    INSERT INTO xml_models (id, user_id, hash, date, time)
    VALUES(modelId, userId, modelHash, modelDate, modelTime);
    SET InsertId = last_insert_id();
    SELECT InsertId;
  END$$

CREATE PROCEDURE
  `proc_getModel`(
  IN modelId INT(11)
)
  BEGIN
    SELECT user_id, hash, date, time FROM xml_models WHERE id = modelId;
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
    SELECT id, hash, date, time FROM xml_models WHERE user_id = userId;
  END $$

CREATE PROCEDURE
`proc_getProjectNameByModelId`(
  IN modelId INT(11)
)
  BEGIN
    SELECT name FROM projects WHERE id = (
      SELECT project_id
      FROM projects_models
      WHERE model_id = modelId
    );
  END $$

CREATE PROCEDURE
`proc_saveModelArray`(
  IN id INT(11),
  IN modelId INT(11),
  IN array LONGTEXT,
  IN date DATE,
  IN time TIME
)
  BEGIN
    INSERT INTO xml_models_arrays VALUES(id, modelId, array, date, time);
  END $$
