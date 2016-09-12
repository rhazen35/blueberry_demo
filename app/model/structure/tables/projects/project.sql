CREATE TABLE `projects`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` VARCHAR(200) NOT NULL,
  `status` VARCHAR(15),
  `date` DATE,
  `time` TIME,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `projects_models`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `project_id` INT(11) NOT NULL,
  `model_id` INT(11) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (model_id) REFERENCES xml_models(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `projects_calculators`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `project_id` INT(11) NOT NULL,
  `calculator_id` INT(11) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (calculator_id) REFERENCES calculators(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `projects_settings`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `project_id` INT(11) NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  `date` DATE,
  `time` TIME,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES projects(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `project_documents`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `project_id` INT(11) NOT NULL,
  `hash` VARCHAR(256) NOT NULL,
  `name` VARCHAR(100),
  `group_name` VARCHAR(100),
  `ext` VARCHAR(10),
  `date` DATE,
  `time` TIME,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES projects(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `project_documents_groups`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `project_id` INT(11) NOT NULL,
  `group_name` VARCHAR(100),
  `date` DATE,
  `time` TIME,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES projects(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
