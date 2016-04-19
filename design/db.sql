CREATE TABLE `user` (

  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,

  `username` varchar(20) NOT NULL,

  `nickname` varchar(20) NOT NULL,

  `citycode` varchar(6) NOT NULL,

  `password` varchar(32) NOT NULL,

  `balance` int NOT NULL DEFAULT 0,

  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`)

);


CREATE TABLE `user_device` (

  `user_id` int UNSIGNED NOT NULL,

  `token` varchar(50) NOT NULL COMMENT 'Auth令牌',

  `login_at` datetime NOT NULL COMMENT '在此设备登录时间',

  `last_login` datetime NOT NULL COMMENT '在此设备末次登录'

);


CREATE TABLE transcation (

  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

  `user_id` int UNSIGNED not null,

  `type` enum('make','give','take','add','sub') not null,

  `relative_id` int UNSIGNED not null,

  `amount` int not null,

  `create_time` TIMESTAMP DEFAULT current_timestamp

);

CREATE TABLE card (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,

  `card_no` varchar(20) NOT NULL,

  `user_id` int UNSIGNED NOT NULL,

  `desc` varchar(30) NOT NULL,

  `create_time` TIMESTAMP DEFAULT current_timestamp
);

