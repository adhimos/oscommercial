USE oscommerce;

CREATE TABLE IF NOT EXISTS `wa_sessions` (
  `session_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `session_key` varchar(255) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `session_starttime` datetime NOT NULL,
  `session_year` int(4) NOT NULL,
  `session_month` int(2) NOT NULL,
  `session_month_string` varchar(15) NOT NULL,
  `session_day` int(2) NOT NULL,
  `session_dayofweek` varchar(15) NOT NULL,
  `session_hour` int(2) NOT NULL,
  `session_min` int(2) NOT NULL,
  `session_week` int(2) NOT NULL,
  `is_repeat_visitor` tinyint(1) NOT NULL,
  `is_new_visitor` tinyint(1) NOT NULL,
  `prior_session_id` bigint(20) DEFAULT NULL,
  `prior_session_datetime` datetime DEFAULT NULL,
  `first_page_url` varchar(1024) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(25) DEFAULT NULL,
  `host` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `is_browser` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`session_id`)
);

CREATE TABLE IF NOT EXISTS `wa_views` (
  `view_ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `view_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`view_ID`)
) ;

ALTER TABLE `customers` ADD `customers_guid` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `customers` ADD `customers_authentication` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `customers` ADD `customers_verified` INT(11) NOT NULL DEFAULT '0' ;

ALTER TABLE `customers_info` ADD  `valid_address` tinyint(1) DEFAULT '1';
ALTER TABLE `customers_info` ADD  `personal_details_valid` tinyint(1) DEFAULT '1';


CREATE TABLE IF NOT EXISTS `chat_room` (
  `room_name` varchar(255) NOT NULL,
  `room_owner` varchar(255) NOT NULL
  PRIMARY KEY (`room_nanme`)
) ;

INSERT INTO `oscommerce`.`chat_room` (`room_name`, `room_owner`) VALUES ('Common Rom', 'admin');