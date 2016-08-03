CREATE TABLE `xml_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hash` varchar(256) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `xml_models_arrays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `array` LONGTEXT NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY(id),
  FOREIGN KEY(model_id) REFERENCES xml_models(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
