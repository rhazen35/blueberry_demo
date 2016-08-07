CREATE TABLE `xml_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `hash` varchar(256) NOT NULL,
  `ext` varchar(10) NOT NULL,
  `valid` varchar(3) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xml_models_db` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY(id),
  FOREIGN KEY(model_id) REFERENCES xml_models(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
