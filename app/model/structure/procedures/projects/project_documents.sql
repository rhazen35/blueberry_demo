CREATE PROCEDURE
`proc_getProjectDocuments`(
  IN projectId INT(11)
)
  BEGIN
    SELECT id, user_id, hash, name, ext, date, time FROM project_documents WHERE project_id = projectId;
  END $$

CREATE PROCEDURE
`proc_getProjectDocumentsGroups`(
  IN projectId INT(11)
)
  BEGIN
    SELECT id, user_id, group, date, time FROM project_documents_groups WHERE project_id = projectId
  END $$

CREATE PROCEDURE
`proc_newProjectDocument`(
  IN id INT(11),
  IN userId INT(11),
  IN projectId INT(11),
  IN hash VARCHAR(256),
  IN name VARCHAR(100),
  IN groupName VARCHAR(100),
  IN ext VARCHAR(10),
  IN date DATE,
  IN time TIME,
  OUT output INT(11)
)
  BEGIN
    INSERT INTO project_documents VALUES(id, userId, projectId, hash, name, groupName, ext, date, time);
    SET output = last_insert_id();
    SELECT output;
  END $$

CREATE PROCEDURE
`proc_newProjectDocumentGroup`(
  IN id INT(11),
  IN userId INT(11),
  IN projectId INT(11),
  IN groupName VARCHAR(100),
  IN date DATE,
  IN time TIME
)
  BEGIN
    INSERT INTO project_documents_groups VALUES(id, userId, projectId, groupName, date, time);
  END $$
