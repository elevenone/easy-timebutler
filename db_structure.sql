CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `password` blob NOT NULL,
  `token` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `full_idx` (`user_id`,`email`,`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
