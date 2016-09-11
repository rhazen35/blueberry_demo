CREATE PROCEDURE
`proc_updateSettings`(
  IN projectId INT(11),
  IN projectName VARCHAR(100),
  IN projectDescr VARCHAR(150),
  IN projectType VARCHAR(10)
)
  BEGIN
    UPDATE projects SET name = projectName, description = projectDescr WHERE id = projectId;
    UPDATE projects_settings SET type = projectType WHERE project_id = projectId;
  END $$