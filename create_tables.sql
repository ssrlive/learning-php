DROP TABLE IF EXISTS `login_log`;
CREATE TABLE `login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'user id',
  `login_ip` varchar(255) NOT NULL DEFAULT '' COMMENT 'login ip',
  `login_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'login time',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='user login log';

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT 'user name',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT 'password',
  `createtime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'create time',
  `updatetime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update time',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='users table';
