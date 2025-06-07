--
-- Table structure for table `daily_tips`
--

CREATE TABLE `daily_tips` (
  `tip_id` int(11) NOT NULL auto_increment,
  `tip_content` text NOT NULL,
  PRIMARY KEY  (`tip_id`),
  UNIQUE KEY `tip_id` (`tip_id`),
  KEY `tip_id_2` (`tip_id`)
) TYPE=MyISAM AUTO_INCREMENT=23 ;

--
-- Dumping data for table `daily_tips`
--

INSERT INTO `daily_tips` VALUES (1, 'To customise your SE experiance, try playing with some of the options on the <b class=b1>Options</b> Page.');
INSERT INTO `daily_tips` VALUES (2, 'You can change your colour scheme at any time from the options page.<br>There are plenty to choose from.');
INSERT INTO `daily_tips` VALUES (3, 'Rule Number One: The Admin Is Always Right.\r\n<br>Rule Number Two: If The Admin Is Wrong, See Rule Number One.\r\n<br>{starfox25, Dec 06 2000 - 14:26 }');
INSERT INTO `daily_tips` VALUES (4, 'Don''t get mad.<br>Get Even!');
INSERT INTO `daily_tips` VALUES (5, 'Just because a ship is more expensive does not necassarily mean it is better.');
INSERT INTO `daily_tips` VALUES (6, 'The only source of knowledge is experience.\r\n<br>{Albert Einstein}');
INSERT INTO `daily_tips` VALUES (7, 'Do not repeat the tactics which have gained you one victory, but let your methods be regulated by the infinite variety of circumstances.\r\n<br>{Sun Tzu, The Art of War - 6:28, 300BC}');
INSERT INTO `daily_tips` VALUES (8, 'Nothing is foolproof to a sufficiently talented fool.\r\n<br>{CrymsonKyng, Apr 21 2001 - 05:56}');
INSERT INTO `daily_tips` VALUES (9, 'You can click on the Mini-map to get a complete picture of the universe.');
INSERT INTO `daily_tips` VALUES (10, 'Clicking a player''s name gives you information about that player.<br>This can also be done with your own name, and will reveal several new options.');
INSERT INTO `daily_tips` VALUES (11, 'The <b>Fleet Diary</b> is a useful place to store information (such as enemy locations, favourite messages, reminder to get a haircut).');
INSERT INTO `daily_tips` VALUES (12, 'Its only a game. Enjoy it.');
INSERT INTO `daily_tips` VALUES (13, 'If you find any bugs, report them to the admin, along with details as to what you where doing to get it.');
INSERT INTO `daily_tips` VALUES (14, 'Autowarp allows you to automatically find your way between A and B. It is not necassarily the shortest route though.');
INSERT INTO `daily_tips` VALUES (15, 'Wormholes offer a great way to get across the universe in only 1 turn (provided there are any around).');
INSERT INTO `daily_tips` VALUES (16, 'Its generally possible to get things on the cheap at Bilkos Auction house. As well as lots of things you can''t get anywhere else in the game.<br>You can get to it from any star-port, or Earth.');
INSERT INTO `daily_tips` VALUES (17, 'Bounties can help turn an turn an enemies allies against him/her.');
INSERT INTO `daily_tips` VALUES (18, 'Upgrades allow you to improve your star-ships, however they cannot be removed once installed.');
INSERT INTO `daily_tips` VALUES (19, 'Joining a Clan can get you new friends and allies, but also new foes.');
INSERT INTO `daily_tips` VALUES (20, 'Statistics about the game you are in can be found by clicking on the games name in the top left corner of the screen (below the date).');
INSERT INTO `daily_tips` VALUES (21, 'You may only own one flagship class ship at a time. If you loose it, the next one will cost double.');
INSERT INTO `daily_tips` VALUES (22, 'Transversers with the <b>Wormhole Stabiliser</b> upgrade are ideal for getting colonists onto your planets quickly and cheaply.');

-- --------------------------------------------------------

--
-- Table structure for table `default_upgrade_loads`
--

CREATE TABLE `default_upgrade_loads` (
  `ship_id` int(11) unsigned NOT NULL default '0',
  `pass_sensor` int(11) unsigned NOT NULL default '0',
  `stand_scanner` int(11) unsigned NOT NULL default '0',
  `adv_scanner` int(11) unsigned NOT NULL default '0',
  `grav_scanner` int(11) unsigned NOT NULL default '0',
  `subs_array` int(11) unsigned NOT NULL default '0',
  `alien_array` int(11) unsigned NOT NULL default '0',
  `stand_cloak` int(11) unsigned NOT NULL default '0',
  `adv_cloak` int(11) unsigned NOT NULL default '0',
  `pir_deflect` int(11) unsigned NOT NULL default '0',
  `spat_unit` int(11) unsigned NOT NULL default '0',
  `dim_flux` int(11) unsigned NOT NULL default '0',
  `laser_cannon` int(11) unsigned NOT NULL default '0',
  `thor_hammer` int(11) unsigned NOT NULL default '0',
  `emp_disruptor` int(11) unsigned NOT NULL default '0',
  `plasma_cannon` int(11) unsigned NOT NULL default '0',
  `einstein_projector` int(11) unsigned NOT NULL default '0',
  `laser_ward` int(11) unsigned NOT NULL default '0',
  `flak_cannon` int(11) unsigned NOT NULL default '0',
  `emp_blaster` int(11) unsigned NOT NULL default '0',
  `silicon_armour` int(11) unsigned NOT NULL default '0',
  `ramscoop` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ship_id`),
  UNIQUE KEY `ship_id` (`ship_id`)
) TYPE=MyISAM;
--
-- Dumping data for table `default_upgrade_loads`
--

INSERT INTO `default_upgrade_loads` VALUES (1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (6, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (7, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (8, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 2, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (9, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 3, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (11, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (12, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 15, 0, 0, 0, 0, 10, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (15, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (16, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 1, 0, 3, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (17, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (18, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (207, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `default_upgrade_loads` VALUES (301, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0);
INSERT INTO `default_upgrade_loads` VALUES (302, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0);
INSERT INTO `default_upgrade_loads` VALUES (303, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 2, 2, 0);
INSERT INTO `default_upgrade_loads` VALUES (304, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `default_upgrade_loads` VALUES (305, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 2, 2, 0);
INSERT INTO `default_upgrade_loads` VALUES (399, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 5, 0, 0, 0, 2, 5, 0);
INSERT INTO `default_upgrade_loads` VALUES (400, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0);
INSERT INTO `default_upgrade_loads` VALUES (401, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 4, 0);
INSERT INTO `default_upgrade_loads` VALUES (402, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 2, 0);
INSERT INTO `default_upgrade_loads` VALUES (403, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 4, 0);
INSERT INTO `default_upgrade_loads` VALUES (404, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `era`
--

CREATE TABLE `era` (
  `era_id` tinyint(4) NOT NULL auto_increment,
  `descrip` text NOT NULL,
  `no_races` tinyint(4) NOT NULL default '1',
  `year` varchar(40) NOT NULL default '0',
  PRIMARY KEY  (`era_id`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

--
-- Dumping data for table `era`
--

INSERT INTO `era` VALUES (1, 'The galaxy is alive with life.  For the first time in decades, man is back in space, and there is nothing but war.<br>The Corporations and Clans fight for territory.<br>In the midst of the fight for space, pirates are claiming abandoned planets and causing general chaos\r\n<br>Will you be the saviour of the Human Race? Or will you cause its downfall?', 2, '2160s');

-- --------------------------------------------------------

--
-- Table structure for table `option_list`
--

CREATE TABLE `option_list` (
  `option_name` varchar(50) NOT NULL default '',
  `option_min` int(11) NOT NULL default '0',
  `option_max` int(11) NOT NULL default '0',
  `option_desc` text NOT NULL,
  `option_type` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`option_name`)
) TYPE=MyISAM;

--
-- Dumping data for table `option_list`
--

INSERT INTO `option_list` VALUES ('news_back', 10, 500, 'Allows you to set how many hours of news will be shown per screen.', 2);
INSERT INTO `option_list` VALUES ('forum_back', 1, 168, 'Allows you to choose how many hours the forum should list per screen.', 2);
INSERT INTO `option_list` VALUES ('show_pics', 0, 1, 'Images are loaded in numerous places throughout the game. They can be turned off here. (This will not effect the Minimap. That can be turned off elsewhere on this page) &&& Hide Pictures. &&& Show Pictures.', 1);
INSERT INTO `option_list` VALUES ('show_minimap', 0, 1, 'The Minimap is the map in the top right corner of the star System. When disbabled, a link to the full map will be shown in it''s place. &&& Minimap Disabled. &&& Minimap Enabled.', 1);
INSERT INTO `option_list` VALUES ('allow_popups', 0, 1, 'Setting this to 0 will mean that no links within the game will create popup windows. &&& No &&& Yes', 1);
INSERT INTO `option_list` VALUES ('show_sigs', 0, 1, 'Signatures are are appended to the end of personal or forum messages sent by another player. <br>Turning them off can make the forums load significantly faster. &&& Signatures Hidden. &&& Signatures Shown.', 1);
INSERT INTO `option_list` VALUES ('show_config', 0, 1, 'Your ship''s Configuration is a collection of two letter code''s that describe what your ship is capable off, such as:<br>sc:ls\r\n<br>Information about what the codes mean can be found in the help.<br>This setting allows you to show or hide the configurations from next to your ships in the star system window. <br>It only really needs to turned on when you have lots of ships of the same type, each of which has been upgraded in a different way.<br>If off the configurations will still be shown elsewhere in the game. But it will save a small amount of bandwidth. &&& Hide Configurations &&& Show Configurations', 1);
INSERT INTO `option_list` VALUES ('show_aim', 0, 1, 'Allows you to show or hide other users AIM contact details. &&& Hide AIM details. &&& Show AIM details.', 1);
INSERT INTO `option_list` VALUES ('show_icq', 0, 1, 'Allows you to show or hide other users ICQ contact details. &&& Hide ICQ details. &&& Show ICQ details.', 1);
INSERT INTO `option_list` VALUES ('planet_report', 0, 2, 'This variable lets you choose what sort of report is returned from the planet after it has been processed: <br><br>Set to 0 to not get a report.<br> Setting it to 1 will result in a report being returned, but only if the planet produces something.<br><br>Set it to 2 to get a report no-matter what happened on it.', 2);
INSERT INTO `option_list` VALUES ('show_clan_ships', 0, 1, 'This option controls whether all clan ships are shown on the clan_control page, or an overview of them. If turned off, the page will load much quicker later in the game.\r\n<br>There is a link in clan control that will allow you to see all clan ships if have the long list disabled. &&& Limited clan ship list shown. &&& Full clan ship list shown.', 1);
INSERT INTO `option_list` VALUES ('show_abbr_ship_class', 0, 1, 'Ship listings in a star system can be made to show only abbreviated ship types (such as MF for Merchant Freighter). All such abbreviations are shown in the help next to the relevant ship. &&& Show full ship type. &&& Show abbreviated ship type.', 1);
INSERT INTO `option_list` VALUES ('show_rel_sym', 0, 1, 'Relations symbols allow a player to see what relation you (or your clan) have set up with another player.<br>This is generally un-nessary for indeps, but a must for clans. &&& Hide relations symbol. &&& Show relations symbol.', 1);
INSERT INTO `option_list` VALUES ('Status_Hidden', '0', '1', 'When you are online, do you want to be shown as... &amp;&amp;&amp; Online &amp;&amp;&amp; Offline', '1');

-- --------------------------------------------------------

--
-- Table structure for table `qbase_challenge_record`
--

CREATE TABLE `qbase_challenge_record` (
  `chal_id` int(11) NOT NULL auto_increment,
  `sessid` varchar(64) NOT NULL default '',
  `challenge` varchar(64) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`chal_id`)
) TYPE=MyISAM AUTO_INCREMENT=432 ;

--
-- Dumping data for table `qbase_challenge_record`
--


-- --------------------------------------------------------

--
-- Table structure for table `qsse_home_news`
--

CREATE TABLE `qsse_home_news` (
  `news_id` int(11) NOT NULL auto_increment,
  `admin_name` varchar(64) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `text` blob NOT NULL,
  `game_id` int(11) NOT NULL default '1',
  PRIMARY KEY  (`news_id`),
  KEY `login_id` (`login_id`),
  KEY `timestamp` (`timestamp`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

--
-- Dumping data for table `qsse_home_news`
--


-- --------------------------------------------------------

--
-- Table structure for table `races`
--

CREATE TABLE `races` (
  `key_id` tinyint(4) NOT NULL default '0',
  `era` tinyint(4) NOT NULL default '0',
  `race_ID` tinyint(4) NOT NULL default '0',
  `planet_name` text NOT NULL,
  `planet_descrip` text NOT NULL,
  `ship` text NOT NULL,
  `equip` text NOT NULL,
  `upgrade` text NOT NULL,
  `colo` text NOT NULL,
  `bank` text NOT NULL,
  `auction` text NOT NULL,
  `used` text NOT NULL,
  `auth` text NOT NULL,
  `img` text NOT NULL,
  `hw_s_name` text NOT NULL,
  `race_name` text NOT NULL,
  `race_descrip` text NOT NULL,
  PRIMARY KEY  (`key_id`)
) TYPE=MyISAM;

--
-- Dumping data for table `races`
--

INSERT INTO `races` VALUES (1, 1, 1, 'Earth', '<b>EARTH</b> - <b class=''b1''>E</b>normous <b class=''b1''>A</b>nd <b class=''b1''>R</b>ound <b class=''b1''>T</b>erran <b class=''b1''>H</b>omeworld', 'Seatogu''s Spacecraft Emporium', 'Wally''s Equipment Shop', 'Vladimirs Accessories & Upgrades Store', 'Colonist Recruitment Center', 'United National Bank', 'Bilko''s Auction House', 'Finnbar''s Used Item Store', 'Sol Authority', '1.jpg', 'Sol', 'United Nations', 'Set up in 2104, the United nations was an effort to bring the human forces back together again after the disaster attack by the unknown aliens 25 years previously.\r\n<br>The damage done during the attack is repaired, and humanity has recovered after the devasting attack, and are again exploring the cosmo.<br>But this time, revenge is on the cards');
INSERT INTO `races` VALUES (2, 1, 2, 'BlackStar Prime', 'The Capital of the Blackstar Goverment', 'Seven Stars Spaceships', 'Explosive Gadegts and Equipment', 'Blackbeard''s Pirate Upgrades', 'Slave Recruitment Center', 'Jolly Roger Bank', 'Maddox''s Auction', 'Second ''Hand'' Items', 'Blackstar Authority', '2.jpg', 'Blackstar', 'Blackstar', 'The few who had survived away from Earth had formed there own goverment, known as Blackstar.<br>They stood against Earth after the disastours attack by the unknown aliens.<br>It is widly belived that the Blackstar operatives were the ones who launched the nuclear weapons that caused what little Earth had left to collapse.');


-- --------------------------------------------------------

--
-- Table structure for table `se_games`
--

CREATE TABLE `se_games` (
  `game_id` tinyint(4) NOT NULL auto_increment,
  `name` varchar(45) NOT NULL default '',
  `db_name` varchar(45) NOT NULL default '',
  `admin_name` varchar(200) NOT NULL default 'Admin',
  `admin_pw` varchar(32) NOT NULL default 'passwd',
  `admin_email` varchar(45) NOT NULL default '',
  `site_address` varchar(45) NOT NULL default '',
  `status` tinyint(4) NOT NULL default '1',
  `paused` tinyint(4) NOT NULL default '1',
  `description` text NOT NULL,
  `intro_message` text NOT NULL,
  `num_stars` int(11) NOT NULL default '150',
  `difficulty` int(11) NOT NULL default '3',
  `last_reset` int(11) NOT NULL default '0',
  `todays_tip` int(11) NOT NULL default '1',
  `game_length_days` int(11) NOT NULL default '30',
  `game_days_remaining` int(11) NOT NULL default '30',
  `sd_day_num` int(11) NOT NULL default '20',
  `day_maint_cnt` int(11) NOT NULL default '1',
  `session_id` int(11) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `paused_day_count` tinyint(2) NOT NULL default '0',
  `game_auto` tinyint(4) NOT NULL default '0',
  `auto_pass` tinyint(2) NOT NULL default '0',
  `teams` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`game_id`),
  UNIQUE KEY `game_id` (`game_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;



--
-- Table structure for table `ship_types`
--

CREATE TABLE `ship_types` (
  `type_id` int(11) unsigned NOT NULL auto_increment,
  `purchase_loc` tinyint(4) NOT NULL default '0',
  `ship_categ` tinyint(4) NOT NULL default '0',
  `race` tinyint(4) NOT NULL default '1',
  `era` tinyint(4) NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `type` varchar(30) NOT NULL default '',
  `class_abbr` varchar(10) NOT NULL default '',
  `cost` int(11) unsigned NOT NULL default '0',
  `tcost` int(11) unsigned NOT NULL default '0',
  `fighters` int(11) unsigned NOT NULL default '0',
  `max_fighters` int(11) unsigned NOT NULL default '0',
  `max_shields` int(11) unsigned NOT NULL default '0',
  `cargo_bays` int(11) unsigned NOT NULL default '0',
  `mine_rate_metal` int(11) unsigned NOT NULL default '0',
  `mine_rate_fuel` int(11) unsigned NOT NULL default '0',
  `mine_rate_darkmatter` int(11) unsigned NOT NULL default '0',
  `mine_rate` int(11) unsigned NOT NULL default '0',
  `descr` text NOT NULL,
  `size` tinyint(4) NOT NULL default '0',
  `config` varchar(30) NOT NULL default '',
  `upgrades` int(11) unsigned NOT NULL default '0',
  `auction` tinyint(4) NOT NULL default '0',
  `move_turn_cost` int(11) unsigned NOT NULL default '1',
  `point_value` int(11) unsigned NOT NULL default '0',
  `tech_bonus` int(11) unsigned NOT NULL default '0',
  `bonus_attack` int(11) unsigned NOT NULL default '0',
  `num_pc` int(11) unsigned NOT NULL default '0',
  `num_ot` int(11) unsigned NOT NULL default '0',
  `num_dt` int(11) unsigned NOT NULL default '0',
  `num_sa` int(11) unsigned NOT NULL default '0',
  `num_ew` int(11) unsigned NOT NULL default '0',
  `num_mi` int(11) unsigned NOT NULL default '0',
  `upg_index` varchar(10) NOT NULL default '',
  `shield_1` int(11) unsigned NOT NULL default '0',
  `shield_2` int(11) unsigned NOT NULL default '0',
  `shield_3` int(11) unsigned NOT NULL default '0',
  `shield_4` int(11) unsigned NOT NULL default '0',
  `shield_5` int(11) unsigned NOT NULL default '0',
  `shield_charge` int(11) unsigned NOT NULL default '0',
  `max_charge` int(11) unsigned NOT NULL default '0',
  `shield_prts` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`type_id`),
  KEY `type_id` (`type_id`)
) TYPE=MyISAM AUTO_INCREMENT=75 ;

--
-- Dumping data for table `ship_types`
--

INSERT INTO `ship_types` VALUES (1, 0, 0, 1, 1, 'Ship Destroyed', '', 'SDestroyed', 10000, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, '', 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `ship_types` VALUES (2, 0, 0, 1, 1, 'Escape Pod', 'Escape Pod', 'EP', 10000, 0, 1, 1, 2, 2, 1, 1, 0, 1, 'If you''re in one of these, you''re pretty darn dead. So hurry up and get a proper ship.', 1, '', 0, 0, 1, 2500, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 2, 0);
INSERT INTO `ship_types` VALUES (3, 1, 1, 1, 1, 'Lycurgos Class', 'Scout Ship', 'SS', 10000, 0, 5, 5, 0, 0, 0, 0, 0, 0, 'Though small, and cheap, this ship has a lot of potential. It''s invaluable for scouting in the random-event games, and games where it warp cost is based on ship size.\r\nIt also has a number of tactical uses.', 1, 'na', 0, 0, 1, 8, 0, 0, 0, 0, 0, 0, 0, 0, '', 1, 0, 0, 0, 0, 0, 500, 0);
INSERT INTO `ship_types` VALUES (4, 1, 2, 0, 1, 'Adalade Class', 'Light Freighter', 'ACLF', 15000, 0, 10, 50, 100, 100, 1, 4, 0, 4, 'Everyones favourite ship, and an old classic.\r\nGood for mining, early attacking, scouting, and pretty much everything else.', 2, 'fr', 0, 0, 1, 15, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 100, 0);
INSERT INTO `ship_types` VALUES (5, 1, 2, 0, 1, 'Horizon Class', 'Medium Freighter', 'HCMF', 35000, 0, 10, 50, 250, 500, 7, 2, 0, 8, 'The strength of this ship is it''s cavernous cargo bays.\r\nIt does however, have to get the cargo capacity from somewhere, and this is done by nearly eliminating the defences. ', 4, 'fr', 0, 0, 3, 45, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 250, 0);
INSERT INTO `ship_types` VALUES (6, 1, 2, 0, 1, 'Harvester Class', 'Mammoth Freighter', 'HCMF', 75000, 0, 10, 50, 500, 900, 5, 12, 0, 16, 'The fastest miner on the market, it also boasts 900 cargo bays and more than adequate defenses.\r\nThis is a well rounded ship with a multitude of purposes.', 5, 'fr:dt', 3, 0, 4, 65, 0, 0, 0, 0, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 600, 0);
INSERT INTO `ship_types` VALUES (7, 1, 3, 1, 1, 'Bristol Class', 'Corvette', 'BCC', 65000, 0, 10, 750, 750, 0, 0, 0, 0, 0, 'The Bristol Class corvette is a fast ship, designed to scout out an area, and engadge in small skirmishes.<br>It carries 2 offensive turrets, including a Laser Cannon and a defensive turret.', 2, 'bs:dt:ot', 5, 0, 3, 30, 0, 0, 0, 2, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 750, 0);
INSERT INTO `ship_types` VALUES (8, 1, 3, 1, 1, 'Moriarty Class', 'Battleship', 'MCB', 195000, 0, 10, 900, 900, 0, 0, 0, 0, 0, 'Heavier than the BCC when it comes to a fight, this ship is capable of holding its own.\r\n<br>High fighter capacity, as well as a scanner, 2 Offensive Turret Arrays and 2 Defensive Turret Arrays, make this a must buy for anyone anticipating a bad day at the office.', 5, 'bs:dt:ot', 4, 0, 4, 40, 0, 0, 0, 2, 2, 0, 0, 0, '', 0, 0, 2, 0, 0, 0, 4000, 4);
INSERT INTO `ship_types` VALUES (9, 1, 3, 1, 1, 'Risk Class', 'Skirmisher', 'RCS', 405000, 0, 10, 1700, 1700, 0, 0, 0, 0, 0, 'If its all out war you want, then this is where you''ll get it. This one has everything any warship ever needed. <br>Lots of firepower (including 3 Offensive Turret Arrays, and 2 Defensive Turret Array). <br>The neighbours will know when you bring one of these home.', 6, 'bs:dt:ot', 2, 0, 5, 50, 0, 0, 0, 3, 2, 0, 0, 0, '', 0, 0, 0, 2, 0, 0, 8000, 4);
INSERT INTO `ship_types` VALUES (11, 1, 6, 1, 1, 'Atom Class', 'Transverser', 'ACTV', 2500000, 0, 10, 50, 0, 0, 0, 0, 0, 0, 'Using the latest Sub-space jump technology, this ship can move fleets anywhere in the Cosmos.  Very good ship for large-scale movements, but also uses alot of turns making the jumps.<br>Has 1 Defensive Turret Array to help protect your investment.', 5, 'sj:dt:na', 3, 0, 4, 30, 0, 0, 0, 0, 1, 0, 0, 0, '', 0, 0, 0, 2, 0, 0, 8000, 2);
INSERT INTO `ship_types` VALUES (12, 1, 10, 1, 1, 'Brobdingnagian Class', 'Warship', 'Brob', 4500000, 0, 10, 5000, 2000, 3000, 0, 0, 0, 0, 'The leviathan of space, and capable of making moons quake, this hulking mass of a ship is the best command ship out there. <br>Comes with built in Quark Disrupter, and thats on top of the excellent offensive/defensive abilities it comes with too (including 15 Offensive Turret Arrays, and 10 Deffensive Turret Arrays).<br>You''ll wonder what you ever did without it.', 8, 'oo:sv:ot:dt', 2, 0, 7, 1000, 0, 0, 0, 5, 5, 0, 0, 0, '', 0, 0, 0, 0, 2, 0, 16000, 4);
INSERT INTO `ship_types` VALUES (16, 1, 3, 2, 1, 'Super Skirmisher', 'Battleship', 'SSkirm', 500000, 0, 10, 2000, 1000, 0, 0, 0, 0, 0, 'A Great ship for getting rid of those pesky enemies, as it incorporates 6 Offensive Turrets, and 6 Defensive Turret Arrays, as well as a high fighter capacity, and lots of shields.', 7, 'sh:bs:ot:dt', 2, 0, 6, 60, 0, 0, 1, 3, 3, 0, 0, 0, '', 0, 0, 0, 0, 2, 0, 16000, 2);
INSERT INTO `ship_types` VALUES (17, 3, 2, 0, 1, 'Mega Miner/Cargo', 'Mega-Flex(tm)', 'MMC', 600000, 0, 0, 0, 0, 0, 12, 12, 0, 12, 'A modular ship just waiting for you to upgrade it to how you want it.', 6, 'fr:dt', 20, 0, 6, 25, 0, 0, 0, 0, 2, 0, 0, 0, '', 0, 0, 0, 3, 0, 0, 12000, 3);
INSERT INTO `ship_types` VALUES (18, 1, 6, 2, 1, 'Warp Class', 'Transverser', 'ATV', 2800000, 0, 10, 50, 0, 0, 0, 0, 0, 0, 'The 8th Wonder of Transport Tech. Excellent for autoshifting, as the wormhole stabiliser comes built in. However it cannot attack, but has 2 Defensive Turret Arrays to ward off enemy ships', 6, 'sj:na:ws:dt', 1, 0, 3, 40, 0, 0, 0, 0, 2, 0, 0, 0, '', 0, 0, 3, 0, 0, 0, 6000, 2);
INSERT INTO `ship_types` VALUES (207, 1, 1, 2, 1, 'Macro Class', 'Scout', 'MCS', 12000, 0, 5, 5, 0, 0, 0, 0, 0, 0, 'A large scoutship that carries a Silicon Armour Module.', 1, 'na:sa', 0, 0, 1, 10, 0, 0, 0, 0, 0, 1, 0, 0, '', 0, 0, 0, 0, 2, 0, 16000, 2);
INSERT INTO `ship_types` VALUES (301, 2, 2, 0, 1, 'Mammoth Mega-Miner', 'BM Freighter', 'HM-R', 120000, 500, 10, 50, 500, 1500, 1, 23, 0, 24, 'The new age of mining has dawned with the introduction of Advanced Mining Technology which increases fuel mining efficiency substantially. The elimination of fighters and most shields allows for more cargo bay space. The Silicon Armour module makes up for most of its defensive shortcomings. Comes with two upgrade pods for slight customisation.', 6, 'fr:sa', 2, 0, 5, 18, 1, 0, 0, 0, 0, 1, 0, 0, '', 0, 0, 0, 3, 0, 0, 12000, 4);
INSERT INTO `ship_types` VALUES (302, 2, 2, 0, 1, 'Mammoth Asteroid Processor', 'BM Freighter', 'HM-A', 120000, 500, 10, 50, 500, 1500, 23, 1, 0, 24, 'Spawned at the same time as the Mammoth Mega-Miner, this ship''s Asteriod Processing facilities allows for the ability to increase metal mining speed to a large degree. The increase in cargo bays has led to the elimination of fighters and most shields, but there was still space to fit a Silicon Armour Matrix. Two upgrade pods allow for slight customisation.', 6, 'fr:sa', 2, 0, 5, 18, 1, 0, 0, 0, 0, 1, 0, 0, '', 0, 0, 0, 3, 0, 0, 12000, 4);
INSERT INTO `ship_types` VALUES (303, 1, 3, 2, 1, 'Saber Class', 'Gunboat', 'SCG', 215000, 0, 10, 900, 200, 0, 0, 0, 0, 0, 'A lightly armed warship that uses 3 Plasma Cannons as its teeth, 2 Silicon Armour Matrices for additional defense, and 2 Electronic Warfare Modules to back those up. This ship is ideal for taking out lightly defended enemy vessels.', 3, 'bs:pc:sa:ew', 1, 0, 2, 35, 0, 0, 3, 0, 0, 2, 2, 0, '', 0, 0, 0, 3, 0, 0, 12000, 3);
INSERT INTO `ship_types` VALUES (304, 2, 8, 0, 1, 'Occultator Class', 'Carrier', 'OCC', 5000000, 500, 10, 10000, 0, 0, 0, 0, 0, 0, 'Welcome to the newest craze in the galaxy! A hollowed out asteroid with an Dilanthi Battlestar''s Ion Propulsion engines anchored to its sides. The cost of this ship reflects the enourmous amount of effort required to remove the asteroids contents and fill it with fighter bays. Its gone from being a navigational hazard for ships, to a planet eliminator, and should you part with your cash, you are guaranteed hours of planet leveling antics. Also comes with a devastating ''Planet Strike'' option where the Occultator itself is powered at immense speed directly through the planet''s atmosphere with easy to imagine consequences.', 7, 'po', 5, 0, 10, 120, 1, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `ship_types` VALUES (305, 2, 3, 0, 1, 'Plasma Cannon Frigate', 'Frigate', 'PCF', 500000, 800, 10, 2100, 1000, 0, 0, 0, 0, 0, 'The new Skirmisher based on Alien tech. Equiped with no less than four Plasma Cannons this master of space is a serious opponent. Also comes with twin Silicon Armour Modules and twin Electronic Warfare Modules. Fighter capacity has been upped with shields being reduced to a minimum. Scanner as standard. This vessal is practically impervious to most light-armed ships.', 6, 'bs:pc:sa:ew', 3, 0, 5, 70, 1, 0, 4, 0, 0, 2, 2, 0, '', 0, 0, 0, 3, 0, 0, 12000, 4);
INSERT INTO `ship_types` VALUES (399, 1, 10, 2, 1, 'Dilanthi Class', 'Battlestar', 'DC-Star', 5000000, 0, 10, 5000, 1000, 500, 0, 0, 0, 0, 'If you thought the Brobdingnagian was the Emperor of Space, think again. This converted alien vessle was found derelict and is a remarkable Flagship, armed to the teeth with an incredible 5 Plasma Cannons, 5 Silicon Armour Modules, 2 Electronic Warfare modules and a Quark Disruptor for good measure. This ship will lead your fleet to battle in true style.<br><p>Note: You may only own ONE flagship at any one time. If you happen to lose it, the cost of a new one will double initially, losing a second one causes the price to triple! The AB-Star is more battle oriented than the Brob which has extra features not found on the AB-Star.', 8, 'oo:sv:pc:sa:ew', 3, 0, 7, 1200, 0, 0, 10, 0, 0, 5, 4, 0, '', 0, 0, 0, 0, 3, 0, 24000, 3);
INSERT INTO `ship_types` VALUES (400, 3, 1, 0, 1, 'Shadow Scout', 'Alien Scoutship', 'SHS', 15000, 100, 10, 50, 50, 50, 0, 0, 0, 0, 'An alien scoutship using dark-matter compression techniques to store 10 times the normal ammount of fighters in a small area.', 1, 'sa', 0, 0, 1, 100, 1, 0, 0, 0, 0, 1, 0, 0, '', 0, 0, 1, 2, 1, 5, 25, 4);
INSERT INTO `ship_types` VALUES (401, 3, 6, 0, 1, 'Man O Warp', 'Alien Transverser', 'MOW', 3000000, 1000, 10, 150, 100, 0, 0, 0, 0, 0, 'An alien transverser with high defensive capabilities including 4 Silicon Armour Modules and 2 Electronic Warfare Packs so that it lasts longer.', 2, 'sj:ws:sa:ew:na', 3, 0, 2, 60, 1, 0, 0, 0, 0, 4, 2, 0, '', 0, 0, 1, 2, 1, 5, 25, 4);
INSERT INTO `ship_types` VALUES (402, 3, 3, 0, 1, 'Shadow Gunboat', 'Alien Warship', 'SGB', 600000, 100, 10, 1500, 250, 0, 0, 0, 0, 0, 'A light alien battleship (well at least light compared to the other ships here) with almost enough capability to take on a skirmisher from sol.  Much cheaper to make.', 2, 'pc:sa:bs', 5, 0, 2, 250, 1, 0, 2, 0, 0, 2, 0, 0, '', 0, 0, 1, 2, 1, 500, 25, 4);
INSERT INTO `ship_types` VALUES (403, 3, 3, 0, 1, 'Shadow Skirmisher', 'Alien Battleship', 'Sh-Skirm', 1200000, 500, 10, 2500, 1000, 0, 0, 0, 0, 0, 'A much more advanced and powerful fightership.  Comes with 4 plasma cannons, and 4 silicon armour moduals.  Planet orbital bombardment abilities.', 3, 'pc:sa:bs', 8, 0, 4, 500, 1, 0, 4, 0, 0, 4, 0, 0, '', 0, 0, 1, 2, 1, 750, 25, 4);
INSERT INTO `ship_types` VALUES (404, 3, 8, 0, 1, 'Shadow Occultator', 'Planetary Assault Ship', 'PAS', 6000000, 600, 10, 10000, 0, 0, 0, 0, 0, 0, 'Mostly used to take down planets with high fighter counts quickly.  Mass driver ability.', 8, 'po:md', 8, 0, 10, 200, 1, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 1, 2, 1, 250, 25, 4);

-- --------------------------------------------------------

--
-- Table structure for table `upgrade_list`
--

CREATE TABLE `upgrade_list` (
  `item_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(96) NOT NULL default '',
  `era` tinyint(4) NOT NULL default '0',
  `race` tinyint(4) NOT NULL default '0',
  `type` tinyint(3) NOT NULL default '0',
  `level` tinyint(3) NOT NULL default '0',
  `seller` tinyint(3) NOT NULL default '0',
  `cost` int(11) unsigned NOT NULL default '0',
  `tech` int(11) unsigned NOT NULL default '0',
  `sql_name` varchar(96) NOT NULL default '',
  `inc` int(11) unsigned NOT NULL default '0',
  `damage` int(11) unsigned NOT NULL default '0',
  `lim` int(11) unsigned NOT NULL default '0',
  `abbrev` varchar(5) NOT NULL default '',
  `info` varchar(96) NOT NULL default '',
  PRIMARY KEY  (`item_id`),
  UNIQUE KEY `item_id` (`item_id`)
) TYPE=MyISAM AUTO_INCREMENT=26 ;

--
-- Dumping data for table `upgrade_list`
--

INSERT INTO `upgrade_list` VALUES (1, 'Fighter Capacity', 1, 0, 1, 1, 1, 5000, 0, 'max_fighters', 300, 0, 0, '', '+300 Fighter Bays');
INSERT INTO `upgrade_list` VALUES (2, 'Shield Capacity', 1, 0, 1, 1, 1, 5000, 0, 'max_shields', 100, 0, 0, '', '+100 Shield Capacity');
INSERT INTO `upgrade_list` VALUES (3, 'Cargo Capacity', 1, 0, 1, 1, 1, 5000, 0, 'cargo_bays', 300, 0, 0, '', '+300 Cargo Capacity');
INSERT INTO `upgrade_list` VALUES (4, 'Shield Charger', 1, 0, 5, 1, 1, 20000, 0, 'shield_charger', 0, 0, 1, 'sh', '2x Shield Charging');
INSERT INTO `upgrade_list` VALUES (5, 'Wormhole Stabiliser', 1, 0, 4, 1, 1, 65000, 0, 'wormhole_stabiliser', 0, 0, 1, 'ws', 'Enable Fleet Sub-Space Warps');
INSERT INTO `upgrade_list` VALUES (6, 'Transwarp Drive', 1, 0, 4, 1, 1, 35000, 0, 'trans_drive', 0, 0, 1, 'tw', 'Enable Transwarp Travelling');
INSERT INTO `upgrade_list` VALUES (7, 'Standard Scanner', 1, 0, 2, 1, 1, 20000, 0, 'stand_scanner', 0, 0, 1, 'ss', '15% Scan Effect');
INSERT INTO `upgrade_list` VALUES (8, 'Advanced Scanner', 1, 0, 2, 2, 1, 40000, 0, 'adv_scanner', 0, 0, 1, 'as', '30% Scan Effect');
INSERT INTO `upgrade_list` VALUES (9, 'Gravitronic Scanner', 1, 0, 2, 3, 2, 80000, 100, 'grav_scanner', 0, 0, 1, 'gs', '45% Scan Effect');
INSERT INTO `upgrade_list` VALUES (10, 'Sub-Space Sensor Array', 1, 0, 2, 4, 2, 160000, 200, 'subs_array', 0, 0, 1, 'ssa', '60% Scan Effect');
INSERT INTO `upgrade_list` VALUES (11, 'Alien Sensor Array', 1, 0, 2, 5, 2, 320000, 300, 'alien_array', 0, 0, 1, 'asa', '75% Scan Effect');
INSERT INTO `upgrade_list` VALUES (12, 'Standard Cloaking Device', 1, 0, 3, 1, 1, 35000, 0, 'stand_cloak', 0, 0, 1, 'sc', '-20% Visibility');
INSERT INTO `upgrade_list` VALUES (13, 'Advanced Cloaking Device', 1, 0, 3, 2, 1, 70000, 0, 'adv_cloak', 0, 0, 1, 'ac', '-35% Visibility');
INSERT INTO `upgrade_list` VALUES (14, 'Pirate Deflector unit', 1, 0, 3, 3, 2, 140000, 100, 'pir_deflect', 0, 0, 1, 'pd', '-50% Visibility');
INSERT INTO `upgrade_list` VALUES (15, 'Spatial Distortion Unit', 1, 0, 3, 4, 2, 280000, 200, 'spat_unit', 0, 0, 1, 'sdu', '-65% Visibility');
INSERT INTO `upgrade_list` VALUES (16, 'Dimensional Flux Unit', 1, 0, 3, 5, 2, 560000, 300, 'dim_flux', 0, 0, 1, 'dfu', '-80% Visibility');
INSERT INTO `upgrade_list` VALUES (17, 'Laser Cannon', 1, 0, 6, 1, 1, 10000, 0, 'laser_cannon', 0, 100, 5, 'lc', '100 Damage (+/- 5%)');
INSERT INTO `upgrade_list` VALUES (18, 'Thor''s Hammer', 1, 0, 6, 2, 1, 20000, 0, 'thor_hammer', 0, 200, 5, 'th', '200 Damage (+/- 10%)');
INSERT INTO `upgrade_list` VALUES (19, 'EMP Disrupter', 1, 0, 6, 3, 2, 40000, 100, 'emp_disruptor', 0, 300, 5, 'ed', '300 Damage (+/- 15%)');
INSERT INTO `upgrade_list` VALUES (20, 'Plasma Cannon', 1, 0, 6, 4, 2, 80000, 200, 'plasma_cannon', 0, 400, 5, 'pc', '400 Damage (+/- 20%)');
INSERT INTO `upgrade_list` VALUES (21, 'Einstein Projector', 1, 0, 6, 5, 2, 160000, 300, 'einstein_projector', 0, 500, 5, 'ep', '500 Damage (+/- 25%)');
INSERT INTO `upgrade_list` VALUES (22, 'Laser Ward', 1, 0, 7, 1, 1, 10000, 0, 'laser_ward', 0, 100, 5, 'lw', '100 Defence (+/- 5%)');
INSERT INTO `upgrade_list` VALUES (23, 'Flak Cannon', 1, 0, 7, 2, 1, 20000, 0, 'flak_cannon', 0, 200, 5, 'fc', '200 Defence (+/- 10%)');
INSERT INTO `upgrade_list` VALUES (24, 'EMP Blaster', 1, 0, 7, 3, 2, 40000, 100, 'emp_blaster', 0, 300, 5, 'eb', '300 Defence (+/- 15%)');
INSERT INTO `upgrade_list` VALUES (25, 'Silicon Matrix Armour', 1, 0, 7, 4, 2, 80000, 200, 'silicon_armour', 0, 400, 5, 'sa', '400 Defence (+/- 20%)');

