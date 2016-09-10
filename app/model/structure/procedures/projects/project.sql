CREATE PROCEDURE
`proc_newProject`(
  IN `projectId` INT(11),
  IN `userId` INT(11),
  IN `projectName` VARCHAR(150),
  IN `projectDescription` VARCHAR(200),
  IN `projectStatus` VARCHAR(15),
  IN `projectDate` DATE,
  IN `projectTime` TIME,
  OUT `InsertId` INT(11)
)
BEGIN
  INSERT INTO projects (id, user_id, name, description, status, date, time)
  VALUES(projectId, userId, projectName, projectDescription, projectStatus, projectDate, projectTime);
  SET InsertId = last_insert_id();
  SELECT InsertId;
END $$

CREATE PROCEDURE
`proc_getProjectById`(
  IN projectId INT(11)
)
  BEGIN
    SELECT name, description, status, date, time FROM projects WHERE id = projectID;
  END $$

CREATE PROCEDURE
  `proc_getProjectByModelId`(
  IN modelId INT(11)
)
  BEGIN
    SELECT id, name, description, status, date, time
    FROM projects
    WHERE id = (
      SELECT project_id
      FROM projects_models
      WHERE model_id = modelId
    );
  END $$

CREATE PROCEDURE
  `proc_getProjectByCalculatorId`(
  IN calculatorId INT(11)
)
  BEGIN
    SELECT id, name, description, status, date, time
    FROM projects
    WHERE id = (
      SELECT project_id
      FROM projects_calculators
      WHERE calculator_id = calculatorId
    );
  END $$

CREATE PROCEDURE
`proc_newProjectModel`(
  IN `id` INT(11),
  IN `userId` INT(11),
  IN `projectId` INT(11),
  IN `modelId` INT(11)
)
  BEGIN
    INSERT INTO projects_models VALUES(id, userId, projectId, modelId);
  END $$

CREATE PROCEDURE
  `proc_newProjectCalculator`(
  IN `id` INT(11),
  IN `userId` INT(11),
  IN `projectId` INT(11),
  IN `calculatorId` INT(11)
)
  BEGIN
    INSERT INTO projects_calculators VALUES(id, userId, projectId, calculatorId);
  END $$

CREATE PROCEDURE
`proc_countProjects`()
  BEGIN
    SELECT COUNT(id) FROM projects;
  END $$

CREATE PROCEDURE
`proc_getAllProjects`()
  BEGIN
    SELECT id, name, description, status, date, time FROM projects ORDER BY date DESC,time DESC;
END $$

CREATE PROCEDURE
`proc_getAllProjectsByUser`(
  IN userID INT(11)
)
  BEGIN
    SELECT id, name, description, status, date, time FROM projects WHERE user_id = userID;
END $$

CREATE PROCEDURE
`proc_getAllProjectsModelsByUser`(
  IN userId INT(11)
)
  BEGIN
    SELECT id, project_id, model_id FROM projects_models WHERE user_id = userId;
  END $$

CREATE PROCEDURE
  `proc_getAllProjectsCalculatorsByUser`(
  IN userId INT(11)
)
  BEGIN
    SELECT id, project_id, calculator_id FROM projects_calculators WHERE user_id = userId;
  END $$

CREATE PROCEDURE
`proc_getModelIdByProjectId`(
  IN projectId INT(11)
)
  BEGIN
    SELECT model_id FROM projects_models WHERE project_id = projectId;
  END $$

CREATE PROCEDURE
`proc_deleteProject`(
  IN projectId INT(11),
  IN modelId INT(11),
  IN calculatorId INT(11)
)
  BEGIN
    DELETE FROM projects_models WHERE project_id = projectId;
    DELETE FROM projects_calculators WHERE project_id = projectId;
    DELETE FROM projects_settings WHERE project_id = projectId;
    DELETE FROM calculators WHERE id = calculatorId;
    DELETE FROM xml_models WHERE id = modelId;
    DELETE FROM projects WHERE id = projectId;
  END $$

CREATE PROCEDURE
  `proc_checkProjectExists`(
  IN projectName VARCHAR(100)
)
    BEGIN
      SELECT id FROM projects WHERE name = projectName;
    END $$

CREATE PROCEDURE
`proc_getLastAddedProject`()
  BEGIN
    SELECT MAX(id) FROM projects;
  END $$

CREATE PROCEDURE
`proc_getLastAddedProjectByUser`(
  IN userId INT(11)
)
  BEGIN
    SELECT MAX(id) FROM projects WHERE user_id = userId;
  END $$

CREATE PROCEDURE
`proc_newProjectSettings`(
  IN id INT(11),
  IN userId INT(11),
  IN projectId INT(11),
  IN type VARCHAR(10),
  IN date DATE,
  IN time TIME
)
  BEGIN
    INSERT INTO projects_settings VALUES(id, userId, projectId, type, date, time);
  END $$

CREATE PROCEDURE
`proc_getProjectSettings`(
  IN projectId INT(11)
)
  BEGIN
    SELECT * FROM projects_settings WHERE project_id = projectId;
  END $$
