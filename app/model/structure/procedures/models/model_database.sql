CREATE PROCEDURE
`proc_newModelDbType`(
  IN modelDbId INT(11),
  IN modelId INT(11),
  IN modelDbType VARCHAR(10),
  IN modelDbName VARCHAR(100),
  IN modelDbDate DATE,
  IN modelDbTime TIME
)
  BEGIN
    INSERT INTO xml_models_db(id, model_id, type, name, date, time) VALUES(modelDbId, modelId, modelDbType, modelDbName, modelDbDate, modelDbTime);
  END $$