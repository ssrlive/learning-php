DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT 'user name',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT 'password',
  `createtime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'create time',
  `updatetime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'update time',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='users table';

INSERT INTO `users` VALUES
  (1,'tezcxzxcst99','123zxczxc456','2024-12-29 00:00:00','2024-06-26 00:00:00'),
  (2,'zhangsan','123ppo456','2024-06-26 00:00:00','2024-06-26 00:00:00'),
  (4,'test2','123ppo456',NULL,NULL),
  (5,'test3','123ppo456',NULL,NULL);
