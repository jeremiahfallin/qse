-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

CREATE TABLE `user_accounts` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_name` varchar(30) NOT NULL default '',
  `passwd` varchar(45) NOT NULL default '',
  `auth` int(11) NOT NULL default '0',
  `first_name` varchar(30) NOT NULL default '',
  `last_name` varchar(30) NOT NULL default '',
  `email_address` varchar(40) NOT NULL default '',
  `icq` int(11) NOT NULL default '0',
  `aim` varchar(50) NOT NULL default '',
  `msn` varchar(50) NOT NULL default '',
  `yim` varchar(50) NOT NULL default '',
  `signed_up` int(11) NOT NULL default '0',
  `session_exp` int(11) NOT NULL default '0',
  `session_id` int(11) NOT NULL default '0',
  `last_login` int(11) NOT NULL default '0',
  `login_count` int(11) NOT NULL default '0',
  `last_ip` varchar(16) NOT NULL default '',
  `num_games_joined` int(11) NOT NULL default '0',
  `total_score` int(11) NOT NULL default '0',
  `con_speed` tinyint(4) NOT NULL default '2',
  `default_color_scheme` tinyint(4) NOT NULL default '1',
  `country` varchar(150) NOT NULL default '',
  `hear_from` varchar(150) NOT NULL default '',
  `e_list` tinyint(4) NOT NULL default '1',
  `birth_date` int(11) NOT NULL default '0',
  `sex` tinyint(4) NOT NULL default '0',
  `hint_question` text NOT NULL,
  `hint_answer` text NOT NULL,
  `newsletter` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`login_id`),
  UNIQUE KEY `login_name` (`login_name`),
  UNIQUE KEY `email_address` (`email_address`)
) TYPE=MyISAM AUTO_INCREMENT=6;

--
-- Dumping data for table `user_accounts`
--

INSERT INTO `user_accounts` VALUES (1, 'Admin', '76a2173be6393254e72ffa4d6df1030a', 0, 'Game Administrator', 'and Overlord', 'Tyrant of the Universe', 0, '', '', '', 1, 1136315044, 11926, 1136314352, 233, '192.168.1.5', 0, 0, 3, 1, '', '', 1, 0, 0, '', '', 0);
INSERT INTO `user_accounts` VALUES (2, 'Aliens', '496444865.479083', -1, 'Created By', 'Jonathan "Moriarty"', 'In--Game AI', 0, '', '', '', 1, 0, 1, 1, 1, '', 0, 953625852, 1, 1, '', '', 1, 0, 0, '', '', 0);
INSERT INTO `user_accounts` VALUES (3, 'Standby', '2462151', -1, 'Standby Account', '', 'Standby Account', 0, '', '', '', 1, 0, 0, 1, 1, '', 0, 0, 1, 1, '', '', 1, 0, 0, '', '', 0);
INSERT INTO `user_accounts` VALUES (4, '2nd Standby', '573947523', -1, '2nd Standby Account', '', '2nd Standby Account', 0, '', '', '', 1, 0, 0, 1, 1, '', 0, 0, 1, 1, '', '', 1, 0, 0, '', '', 0);
INSERT INTO `user_accounts` VALUES (5, 'Dev', '1eff925bfa9d1def8e8f29a5a09f884b', 0, 'Developer', 'Developer', '', 0, '', '', '', 1113842934, 1133467848, 5255, 1133465073, 50, '127.0.0.1', 3, 0, 2, 1, 'ap', '', 1, 0, 0, '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_history`
--

CREATE TABLE `user_history` (
  `login_id` int(11) NOT NULL default '0',
  `timestamp` int(11) NOT NULL default '0',
  `game_db` varchar(30) NOT NULL default '',
  `action` text NOT NULL,
  `user_IP` varchar(16) NOT NULL default '',
  `other_info` text NOT NULL
) TYPE=MyISAM;
--
-- Dumping data for table `user_history`
--


-- --------------------------------------------------------