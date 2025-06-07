-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_admin_ships`
--
DROP TABLE IF EXISTS QUANTUM_admin_ships;
CREATE TABLE `QUANTUM_admin_ships` (
  `ship_type_id` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`ship_type_id`),
  UNIQUE KEY `ship_type_id` (`ship_type_id`)
) Type=MyISAM;

--
-- Dumping data for table `QUANTUM_admin_ships`
--

INSERT INTO `QUANTUM_admin_ships` VALUES (1, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (2, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (3, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (4, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (5, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (6, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (7, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (8, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (9, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (11, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (12, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (15, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (16, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (17, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (18, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (207, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (301, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (302, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (303, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (304, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (305, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (399, 1);
INSERT INTO `QUANTUM_admin_ships` VALUES (400,1);
INSERT INTO `QUANTUM_admin_ships` VALUES (401,1);
INSERT INTO `QUANTUM_admin_ships` VALUES (402,1);
INSERT INTO `QUANTUM_admin_ships` VALUES (403,1);
INSERT INTO `QUANTUM_admin_ships` VALUES (404,1);

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_bank_account`
--
DROP TABLE IF EXISTS QUANTUM_bank_account;

CREATE TABLE `QUANTUM_bank_account` (
  `login_id` int(11) NOT NULL default '0',
  `loan` int(11) NOT NULL default '0',
  `deposit` int(11) NOT NULL default '0',
  PRIMARY KEY  (`login_id`)
) Type=MyISAM;

--
-- Dumping data for table `QUANTUM_bank_account`
--
INSERT INTO `QUANTUM_bank_account`VALUES (1, 0, 0);


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_bilkos`
--
DROP TABLE IF EXISTS QUANTUM_bilkos;
CREATE TABLE `QUANTUM_bilkos` (
  `item_id` int(11) unsigned NOT NULL auto_increment,
  `item_name` varchar(30) NOT NULL default '',
  `item_code` varchar(30) NOT NULL default '',
  `item_type` int(11) NOT NULL default '0',
  `bidder_id` int(11) NOT NULL default '0',
  `going_price` int(11) NOT NULL default '0',
  `timestamp` int(11) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '0',
  `descr` text NOT NULL,
  PRIMARY KEY  (`item_id`),
  UNIQUE KEY `item_id` (`item_id`)
) Type=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `QUANTUM_bilkos`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_powerups`
--

CREATE TABLE QUANTUM_powerups (
  id int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default 'Junk',
  amount bigint(255) NOT NULL default '0',
  `type` varchar(255) NOT NULL default 'cash',
  `table` varchar(255) NOT NULL default 'users',
  user_relation varchar(255) NOT NULL default 'login_id',
  location bigint(255) NOT NULL default '0',
  `desc` varchar(255) NOT NULL default 'Looks like you found useless junk, too bad.',
  PRIMARY KEY  (id)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_powerups`
--
-- --------------------------------------------------------



--
-- Table structure for table `QUANTUM_bmrkt`
--
DROP TABLE IF EXISTS QUANTUM_bmrkt;
CREATE TABLE `QUANTUM_bmrkt` (
  `bmrkt_id` int(11) unsigned NOT NULL auto_increment,
  `location` int(11) NOT NULL default '0',
  `tech_variance` int(11) NOT NULL default '0',
  `bmrkt_type` tinyint(4) NOT NULL default '0',
  `bm_name` varchar(30) NOT NULL default '',
  `bm_move` int(11) NOT NULL default '1',
  PRIMARY KEY  (`bmrkt_id`),
  KEY `planet_id` (`bmrkt_id`),
  KEY `location` (`location`)
) Type=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `QUANTUM_bmrkt`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_clan_forum`
--
DROP TABLE IF EXISTS QUANTUM_clan_forum;
CREATE TABLE `QUANTUM_clan_forum` (
  `message_id` int(11) unsigned NOT NULL auto_increment,
  `sender_name` varchar(30) NOT NULL default '',
  `timestamp` int(11) unsigned NOT NULL default '0',
  `login_id` int(11) unsigned NOT NULL default '0',
  `clan_id` int(11) unsigned NOT NULL default '0',
  `subject` varchar(50) NOT NULL default '',
  `text` blob NOT NULL,
  `sender_id` int(11) unsigned NOT NULL default '1',
  `reply_to` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`message_id`),
  KEY `login_id` (`login_id`),
  KEY `timestamp` (`timestamp`)
) Type=MyISAM AUTO_INCREMENT=4 ;

--
-- Dumping data for table `QUANTUM_clan_forum`
--

--
-- Table structure for table `QUANTUM_clan_relations`
--

CREATE TABLE QUANTUM_clan_relations (
  relation_id int(11) NOT NULL auto_increment,
  clan_id int(11) NOT NULL default '0',
  v_clan_id int(11) NOT NULL default '0',
  nap_agree tinyint(4) NOT NULL default '0',
  `type` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (relation_id),
  UNIQUE KEY relation_id (relation_id)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_clan_relations`
--

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_clans`
--
DROP TABLE IF EXISTS QUANTUM_clans;
CREATE TABLE `QUANTUM_clans` (
  `clan_id` int(11) unsigned NOT NULL auto_increment,
  `clan_name` varchar(30) NOT NULL default '',
  `passwd` varchar(25) NOT NULL default '',
  `leader_id` int(11) NOT NULL default '0',
  `members` int(11) NOT NULL default '1',
  `symbol` char(3) NOT NULL default '',
  `sym_color` varchar(6) NOT NULL default '',
  `clan_score` int(11) NOT NULL default '0',
  `fighter_kills` int(11) NOT NULL default '0',
  PRIMARY KEY  (`clan_id`),
  KEY `login_id` (`clan_id`,`clan_name`)
) Type=MyISAM AUTO_INCREMENT=10 ;

--
-- Dumping data for table `QUANTUM_clans`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_db_vars`
--
DROP TABLE IF EXISTS QUANTUM_db_vars;
CREATE TABLE `QUANTUM_db_vars` (
  `type` tinyint(3) NOT NULL default '0',
  `name` varchar(30) NOT NULL default '0',
  `value` varchar(30) NOT NULL default '0',
  `min` int(11) NOT NULL default '1',
  `max` int(11) unsigned NOT NULL default '1',
  `descript` text NOT NULL,
  PRIMARY KEY  (`name`)
) Type=MyISAM;

--
-- Dumping data for table `QUANTUM_db_vars`
--

INSERT INTO QUANTUM_db_vars VALUES (1,'admin_var_show','1',0,1,'If 0, players cannot see the game vars, on the game_vars page.');
INSERT INTO QUANTUM_db_vars VALUES (1,'allow_search_map','1',0,1,'Determines if users are allowed to run searches to try and find a system on the map (best left for the server admin to set).');
INSERT INTO QUANTUM_db_vars VALUES (1,'allow_signatures','1',0,1,'If set to 1, posts will have the signatures on them. Otherwise, they will be turned off.');
INSERT INTO QUANTUM_db_vars VALUES (4,'alternate_play_1','1',0,1,'Determines if all ships can mine everything, or each ship mines either metal or fuel. <br>Players will not be able to buy metal or fuel, so will have to mine everything they plan to use.<br>Set to 0 to have original play style, and 1 to have alternate play style.');
INSERT INTO QUANTUM_db_vars VALUES (2,'bilkos_time','12',6,72,'The amount of hours a player must hold a bid on an item at bilkos for, before it can be won.');
INSERT INTO QUANTUM_db_vars VALUES (4,'buy_elect','2100',1,2500,'Price Electronics can be brought for. Sell price is 20% less.<br><br>Admin Note: Quantum Star SE uses a different set of conditions controlling Electronic production, see planet_elect note.');
INSERT INTO QUANTUM_db_vars VALUES (4,'buy_fuel','100',1,2000,'Price Fuel can be brought for. Sell price is 20% less.');
INSERT INTO QUANTUM_db_vars VALUES (4,'buy_metal','120',1,2000,'Price Metal can be brought for. Sell price is 20% less.');
INSERT INTO QUANTUM_db_vars VALUES (4,'buy_organ','10',1,2000,'Price Organics can be brought for. Sell price is 20% less.');
INSERT INTO QUANTUM_db_vars VALUES (2,'clan_member_limit','10',0,100,'Max number of players able to join a single clan.');
INSERT INTO QUANTUM_db_vars VALUES (5,'cost_bomb','1500000',0,10000000,'Cost of the normal bombs. Other bombs will cost a multiple of this number.\r\n<br>(SN is 5x the price of a normal bomb.<br> Genesis Device is 20x the cost of a normal bomb. <br>Terra Imploder is 20x the cost of a normal bomb)');
INSERT INTO QUANTUM_db_vars VALUES (2,'cost_colonist','50',0,10000,'Cost per colonist, as taken from Earth');
INSERT INTO QUANTUM_db_vars VALUES (1,'cost_to_post','0',0,100,'Cost (in turns) to post a message to the forum.');
INSERT INTO QUANTUM_db_vars VALUES (1,'count_days_left_in_game','90',0,10000,'Number of days left before reseting.  Counts down every night.');
INSERT INTO QUANTUM_db_vars VALUES (1,'enable_politics','0',0,1,'Determines if Politics are enabled or not. Set to 0 to disable.');
INSERT INTO QUANTUM_db_vars VALUES (5,'enable_superweapons','1',0,1,'Setting this to 0 will mean the terra maelstrom, and the omega missile will be turned off. <br>Useful if you want very big planets in your game, or if using the uv_planets variable for a limited planet count within the game.');
INSERT INTO QUANTUM_db_vars VALUES (5,'fighter_cost_earth','400',1,10000,'The cost to buy a fighter at earth.');
INSERT INTO QUANTUM_db_vars VALUES (5,'flag_bomb','2',0,2,'If set to 1, bombs cannot be purchased from equip shop. If set to 2, then only the delta bomb will appear in Bilkos.');
INSERT INTO QUANTUM_db_vars VALUES (7,'flag_mines','0',0,1,'Mines Flag. To enable Mines & Leeches set to 1. Automatically disabled when Research flag is set to 0.');
INSERT INTO QUANTUM_db_vars VALUES (8,'flag_planet_attack','1',0,1,'Planet attack flag.  When set to 0 planets can not be attacked.');
INSERT INTO QUANTUM_db_vars VALUES (7,'flag_research','1',0,1,'Research flag. When set to 0 research is disabled. Also disables the Blackmarket in the game. To enable, set to 1.');
INSERT INTO QUANTUM_db_vars VALUES (8,'flag_sol_attack','0',0,1,'If set to 0 then attacking at Sol is dissallowed. Bombs are not allowed either.');
INSERT INTO QUANTUM_db_vars VALUES (8,'flag_space_attack','1',0,1,'Space attack flag.  When set to 0 ships can not be attacked.');
INSERT INTO QUANTUM_db_vars VALUES (2,'hourly_shields','20',0,100,'Number of shield points regenerated per ship each hour.');
INSERT INTO QUANTUM_db_vars VALUES (7,'hourly_tech','10',0,100,'Number of Tech Units generated each hour by each planetary Research Facility. Increases by increments due to colony population growth to a maximum of 3 times the hourly rate per Facility.');
INSERT INTO QUANTUM_db_vars VALUES (2,'hourly_turns','20',0,1000,'Number of turns gained each hour.');
INSERT INTO QUANTUM_db_vars VALUES (8,'keep_sol_clear','1',0,1,'If 1 then will scatter all non-newbie ships from Sol if they are in that system for two consecutive hours.');
INSERT INTO QUANTUM_db_vars VALUES (2,'max_clans','100',0,10000,'Max number of clans that can be created.');
INSERT INTO QUANTUM_db_vars VALUES (2,'max_players','1000',0,100000,'Max number of players that can be signed up in the game.');
INSERT INTO QUANTUM_db_vars VALUES (2,'max_ships','100',0,1000,'Max number of ships that a player can have.');
INSERT INTO QUANTUM_db_vars VALUES (2,'max_turns','5000',10,1000000,'Max number of turns a player can have.');
INSERT INTO QUANTUM_db_vars VALUES (1,'message_colour','3',0,4,'Colour of forum & private messages of admin only: 1=yellow, 2=blue, 3=green, 4=red');
INSERT INTO QUANTUM_db_vars VALUES (2,'min_before_transfer','1',0,10000,'Min number of days before players can transfer cash/ships.');
INSERT INTO QUANTUM_db_vars VALUES (8,'new_logins','1',0,1,'New login flag. When set to 0, new players cannot sign-up.');
INSERT INTO QUANTUM_db_vars VALUES (1,'one_comp_one_user','2',0,2,'If 0, then only one user per comp; if 1 then many users, but admin gets told; if 2 then many users per comp, no warnings');
INSERT INTO QUANTUM_db_vars VALUES (6,'planet_attack_turn_cost','10',0,1000,'Number of turns it takes to attack a planet.');
INSERT INTO QUANTUM_db_vars VALUES (4,'planet_elect','1',1,10000,'The number of electronics a user gets produced from 500 assigned colonists using 10 metal and 10 fuel. <br><br>Admin Note: Quantum Star SE uses a triple daily maint system. To retain balance Electronics differ from Normal SE in two regards. 1) their default cost is 2100 credits 2) their default production rate is 1 not 8. Changing these back to normal SE values will increase Fighter counts by a large factor.');
INSERT INTO QUANTUM_db_vars VALUES (4,'planet_fighters','10',1,10000,'The number of fighters a user gets produced from 100 assigned colonists using 1 metal, 1 fuel, and 1 electronic.');
INSERT INTO QUANTUM_db_vars VALUES (4,'planet_organ','500',1,1000000,'The number of colonists required to produce 1 unit of organics.');
INSERT INTO QUANTUM_db_vars VALUES (2,'random_events','3',0,3,'The higher the number, the more events. If 0 then they are turned off.');
INSERT INTO QUANTUM_db_vars VALUES (3,'rr_fuel_chance','5',0,100,'Chance that a star system will recieve random amount of fuel daily.');
INSERT INTO QUANTUM_db_vars VALUES (3,'rr_fuel_chance_max','500',0,1000000,'Maximum amount of fuel that a system will recieve.');
INSERT INTO QUANTUM_db_vars VALUES (3,'rr_fuel_chance_min','100',0,1000000,'Minimum amount of fuel that a system will recieve.');
INSERT INTO QUANTUM_db_vars VALUES (3,'rr_metal_chance','5',0,100,'Chance that a star system will recieve random amount of metal daily.');
INSERT INTO QUANTUM_db_vars VALUES (3,'rr_metal_chance_max','300',0,1000000,'Maximum amount of metal that a system will recieve.');
INSERT INTO QUANTUM_db_vars VALUES (3,'rr_metal_chance_min','50',0,1000000,'Minimum amount of metal that a system will recieve.');
INSERT INTO QUANTUM_db_vars VALUES (2,'score_method','3',0,3,'Decides method of scoring used.<br><br>0: Scores are Off<br>1: Score is based on fighter/ship kills.<br>2: Score is based on point value of ships killed and lost. (Not Recommended)<br>3: Score based on Total Net Worth.<br>4: Score takes just about everything into account. (Not Implemented) Most accurate are 1 and 3.');
INSERT INTO QUANTUM_db_vars VALUES (2,'ship_warp_cost','-1',-1,1000,'This var determines how much it costs for players to warp between systems.<br><br>Set it between 0 and 1000 to determine the number of turns,<br>OR<br>set it to -1, whereby a different system will be used, where different ship types take different numbers of turns to get to places. The bigger the ship the more turns it takes.');
INSERT INTO QUANTUM_db_vars VALUES (6,'space_attack_turn_cost','2',0,1000,'Number of turns it takes to attack another ship.');
INSERT INTO QUANTUM_db_vars VALUES (2,'start_cash','0',0,1000000,'Amount of cash a player starts out with.');
INSERT INTO QUANTUM_db_vars VALUES (2,'start_ship','4',3,6,'Ship player starts in. 3 = SS, 4 = MF, 5 = ST, 6= HM');
INSERT INTO QUANTUM_db_vars VALUES (2,'start_tech','0',0,1000000,'Number of tech support units a player starts out with. Recommended as zero, unless running an Extreme Game.');
INSERT INTO QUANTUM_db_vars VALUES (2,'start_turns','50',0,1000000,'Amount of turns a player starts out with.');
INSERT INTO QUANTUM_db_vars VALUES (8,'sudden_death','0',0,1,'When this is set to 1, players can never regenerate, nor can new players join the game. To automatically enter SD after the sd_day_num amount of days set in se_games table of the DB, set this to -1. This will automatically throw the game into SD without needing manual input');
INSERT INTO QUANTUM_db_vars VALUES (8,'turns_before_attack','50',0,1000,'Turns that have to be used before a new account can attack ships.');
INSERT INTO QUANTUM_db_vars VALUES (8,'turns_before_planet_attack','50',0,1000,'Turns that a player has to use before they can attack/use planets.');
INSERT INTO QUANTUM_db_vars VALUES (8,'turns_safe','50',0,1000,'Turns that have to pass before a new player can be attacked.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_fuel_max','800',1,1000000,'Max amount of fuel in a star system when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_fuel_min','400',1,1000000,'Min amount of fuel in a star system when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_fuel_percent','20',0,100,'Percent of star systems that will have fuel when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_map_layout','3',0,6,'Choose the layout of the map.<p>0 = Random Star Distribution.<br>1 = Grid of stars.<br>2 = Galactic Core<p>Note: It is best to set the <b>uv_min_star_dist</b> to a lower value for the non-random layouts.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_metal_max','400',1,1000000,'Max amount of metal in a star system when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_metal_min','100',1,1000000,'Min amount of metal in a star system when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_metal_percent','10',0,100,'Percent of star systems that will have metal when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_min_star_dist','15',10,20,'Minimum distance between star systems - in pixels.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_needs_gen','0',0,1,'Universe generation flag.  When this is set to 1 the universe will be (re)created when the next daily maint is run.');
INSERT INTO QUANTUM_db_vars VALUES (7,'uv_num_bmrkt','5',0,100,'Sets number of blackmarkets created during Universe generation.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_num_ports','25',0,300,'Number of star ports when universe is generated.\r\n<br>The value is generated with extras created in the homeworld systems');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_num_stars','250',25,1500,'Number of stars in the universe.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_planets','-1',-1,1000,'This variable determines how many planets should be created in the universe when the universe is generated.<br>Set to -1 to make it so as the universe starts with none and players have to make them using genesis devices.<br>Set to anything else to stipulate that as the number of planets that will be generated with the universe.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_port_variance','10',0,100,'Amount of variance in prices at star ports. <*>');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_show_warp_numbers','1',0,1,'Show warp numbers.  When set to 0 warp numbers will not be shown on starmaps after universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_universe_size','500',200,2500,'Size in pixels of the universe.');
INSERT INTO QUANTUM_db_vars VALUES (3,'wormholes','1',0,2,'Set to 0 disable Wormholes, 1 to have them in the game but not on the Map, and 2 to have them in the game & on the Map.');
INSERT INTO QUANTUM_db_vars VALUES (7,'flag_dmatter','0',0,1,'Dark-Matter flag. When set to 0 Dark-Matter, an add-on for Blackmarkets is disabled. Automatically disabled if the Research flag is set to 0.');
INSERT INTO QUANTUM_db_vars VALUES (7,'uv_dark_max','0',0,1000000,'Max amount of Dark Matter in a star system when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (7,'uv_dark_min','0',0,1000000,'Min amount of Dark Matter in a star system when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (7,'uv_dark_percent','0',0,100,'Percent of star systems that will have Dark Matter when universe is generated.');
INSERT INTO QUANTUM_db_vars VALUES (7,'buy_darkmatter','0',1,1000,'Purchase price per unit of Dark-Matter. Sale price is 20% less.');
INSERT INTO QUANTUM_db_vars VALUES (7,'buy_tech','1000',100,1000,'Purchase price per Tech Unit. Sale of Tech Units unavailable.');
INSERT INTO QUANTUM_db_vars VALUES (7,'flag_fines','1',0,1,'Fines Flag. Set to 1 to allow Sol Authority fines be imposed on players using Blackmarkets. Automatically disabled if Research flag is set to 0.');
INSERT INTO QUANTUM_db_vars VALUES (7,'flag_bmrkt','1',0,1,'Blackmarket Flag. When set to 0 the Blackmarket module is disabled. To enable, set to 1.');
INSERT INTO QUANTUM_db_vars VALUES (7,'flag_sa','1',0,1,'Sol Authority Flag. When set to 0 Sol Authority Compounds are disabled (also disables fines, etc). To enable, set to 1.');
INSERT INTO QUANTUM_db_vars VALUES (1,'day_maint_count','3',1,10,'Count daily maints. Enter the number of times the run_daily.pl maint is used during any 24 hour period. Used to make certain timing functions fully accurate.');
INSERT INTO QUANTUM_db_vars VALUES (2,'alternate_bounty_sys','1',0,1,'Alternate Bounty System. When set to 1 an alternate system is used where the destruction of a players fleet causing them to be placed in an Escape Pod will allow the attacker claim 50% of the outstanding bounty. The remaining 50% is claimed by whoever destroys the Escape Pod');
INSERT INTO QUANTUM_db_vars VALUES (7,'flag_raids','0',0,1,'Raiding flag. When set to 1 raiding and ship claiming is enabled. Automatically disabled when flag_bmrkt is set to 0.');
INSERT INTO QUANTUM_db_vars VALUES (7,'ramscoop_turncost','0',0,25,'Number of extra turns each warp to a star system costs when ramscoops are turned on. Recommended as 4 to limit their use.');
INSERT INTO QUANTUM_db_vars VALUES (7,'banned_mine_dist','6',4,25,'This sets the distance around Sol System as measured in warps, that Mines are banned from being laid. If too low, Mines may cause havoc for players attempting to reach Earth so set wisely.');
INSERT INTO QUANTUM_db_vars VALUES (1,'rejoin_delay','0',0,1,'When set to 1 enables a 24hr delay between a user retiring from a game and rejoining the same game. Can be used to prevent a cheat where players exploring the universe, retiring, and finally rejoining again to start with normal turns. This option is controlled from the admin menu so leave as 0 and enable from there.');
INSERT INTO QUANTUM_db_vars VALUES (2,'fleet_war_max','5',0,50,'Determines the maximum number of battleships allowable per fleet');
INSERT INTO QUANTUM_db_vars VALUES (1,'show_news','1',0,1,'Choose whether to display the top news headlines on the main star system pages.');
INSERT INTO QUANTUM_db_vars VALUES (6,'hostile_planets','1',0,1,'Set to 1 will enable hostile planets, at zero hostile planets are disabled and all star systems regardless of planets are visible.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_planet_slots','3',1,50,'This sets the maximum number of planet slots that may appear (randomly) in a system when the universe is generated.<br>Note: If <b class=b1>uv_planets</b> is set to anything other than -1, this variable will be ignored.');
INSERT INTO QUANTUM_db_vars VALUES (2,'max_fleet_size','10',10,50,'Determines the maximum size of all fleets in QS. Other vars determine max warships in a fleet, remaining slots open only to non warships (or alternatively to war-capable ships unable to attack ships by themselves.');
INSERT INTO QUANTUM_db_vars VALUES (5,'shield_cost','350',0,10000,'Cost of a shield in earth');
INSERT INTO QUANTUM_db_vars VALUES (8,'game_auto','0',0,1,'Will the game run itself?\r\n1 = yes.  0 = no');
INSERT INTO QUANTUM_db_vars VALUES (9,'era','1',1,2,'What era should the game be in?\r\n<br>Era Info coming soon');
INSERT INTO QUANTUM_db_vars VALUES (9,'Homeworld','1',0,1,'Are the homeworlds active?\r\n<br>Description coming soon');
INSERT INTO QUANTUM_db_vars VALUES (9,'max_races','2',1,2,'The number of races that are in the game');
INSERT INTO QUANTUM_db_vars VALUES (2,'bank','0',0,1,'Is the bank on (set to 1) or off (0) in this game?');
INSERT INTO QUANTUM_db_vars VALUES (2,'intrest','10',0,10,'What is the intrest value for the bank?');
INSERT INTO QUANTUM_db_vars VALUES (2,'flag_aliens','0',0,1,'Set to 1 will enable aliens in the game.');
INSERT INTO QUANTUM_db_vars VALUES (2,'alien_res_min','50000',1000,1000000000,'Set the MIN amount of what alien planet resources will be based on');
INSERT INTO QUANTUM_db_vars VALUES (2,'alien_res_max','100000',1000,1000000000,'Set the MAX amount of what alien planet resources will be based on');
INSERT INTO QUANTUM_db_vars VALUES (2,'alien_max_planets','0',1,10000,'Set the MAX amount of alien planets');
INSERT INTO QUANTUM_db_vars VALUES (5,'flag_sn_effecter','0',0,1,'If set to 1, the supernova effecter will appear in the homeworld eqipment shops, if bombs are on.');
INSERT INTO QUANTUM_db_vars VALUES (3,'uv_num_shipyards','3',0,100,'Number of Alien ShipYards when universe is generated.\r\n');
INSERT INTO QUANTUM_db_vars VALUES (2,'flag_trading','0',0,1,'Enables weather port prices change based on the uv_port_variance. 1 = on 0 = off');

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_diary`
--
DROP TABLE IF EXISTS QUANTUM_diary;
CREATE TABLE `QUANTUM_diary` (
  `entry_id` int(11) unsigned NOT NULL auto_increment,
  `timestamp` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `entry` blob NOT NULL,
  `topic` blob NOT NULL,
  PRIMARY KEY  (`entry_id`),
  UNIQUE KEY `entry_id` (`entry_id`),
  KEY `entry_id_2` (`entry_id`,`timestamp`)
) Type=MyISAM AUTO_INCREMENT=2 ;

--
-- Dumping data for table `QUANTUM_diary`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_fines`
--
DROP TABLE IF EXISTS QUANTUM_fines;
CREATE TABLE `QUANTUM_fines` (
  `fine_id` int(11) unsigned NOT NULL auto_increment,
  `login_id` int(11) NOT NULL default '0',
  `login_name` varchar(30) NOT NULL default '',
  `amount` int(11) NOT NULL default '0',
  `bounty` int(11) NOT NULL default '0',
  `overdue` tinyint(4) NOT NULL default '0',
  `paid` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`fine_id`),
  UNIQUE KEY `nap_id` (`fine_id`)
) Type=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `QUANTUM_fines`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_fleets`
--
DROP TABLE IF EXISTS QUANTUM_fleets;
CREATE TABLE `QUANTUM_fleets` (
  `fleet_id` int(11) unsigned NOT NULL auto_increment,
  `fleet_name` varchar(30) NOT NULL default '',
  `fleet_num` int(11) unsigned NOT NULL default '0',
  `login_name` varchar(30) NOT NULL default '',
  `login_id` int(11) unsigned NOT NULL default '0',
  `clan_id` int(11) unsigned NOT NULL default '0',
  `fleet_link` int(11) unsigned NOT NULL default '0',
  `location` int(11) unsigned NOT NULL default '0',
  `ship_id` int(11) unsigned NOT NULL default '0',
  `ramscoop` int(11) unsigned NOT NULL default '0',
  `scatter` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`fleet_id`),
  KEY `clan_id` (`clan_id`),
  KEY `login_id` (`login_id`)
) Type=MyISAM AUTO_INCREMENT=18 ;

--
-- Dumping data for table `QUANTUM_fleets`
--

INSERT INTO `QUANTUM_fleets` VALUES (2, 'Admin', 1, 'Admin', 1, 0, 0, 1, 2, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_game_forum`
--

DROP TABLE IF EXISTS QUANTUM_game_forum;
CREATE TABLE QUANTUM_game_forum (
  message_id int(11) unsigned NOT NULL auto_increment,
  sender_name varchar(30) NOT NULL default '',
  timestamp int(11) unsigned NOT NULL default '0',
  login_id int(11) unsigned NOT NULL default '0',
  subject varchar(50) NOT NULL default '',
  text blob NOT NULL,
  sender_id int(11) unsigned NOT NULL default '1',
  clan_id int(11) unsigned NOT NULL default '0',
  reply_to int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (message_id),
  KEY login_id (login_id),
  KEY timestamp (timestamp)
) TYPE=MyISAM;

--
-- Dumping data for table `QUANTUM_game_forum`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_indices`
--
DROP TABLE IF EXISTS QUANTUM_indices;
CREATE TABLE `QUANTUM_indices` (
  `index_id` int(11) unsigned NOT NULL auto_increment,
  `metal` int(11) unsigned NOT NULL default '1000',
  `fuel` int(11) unsigned NOT NULL default '1000',
  `organ` int(11) unsigned NOT NULL default '1000',
  `elect` int(11) unsigned NOT NULL default '1000',
  PRIMARY KEY  (`index_id`),
  KEY `metal` (`metal`),
  KEY `fuel` (`fuel`),
  KEY `organ` (`organ`),
  KEY `elect` (`elect`)
) Type=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `QUANTUM_indices`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_leeches`
--

CREATE TABLE QUANTUM_leeches (
  leech_id int(11) unsigned NOT NULL auto_increment,
  login_id int(11) NOT NULL default '0',
  task tinyint(4) NOT NULL default '0',
  ship_id int(11) NOT NULL default '0',
  shield int(11) NOT NULL default '0',
  PRIMARY KEY  (leech_id),
  UNIQUE KEY leech_id (leech_id)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_leeches`


-- --------------------------------------------------------

CREATE TABLE QUANTUM_messages (
  message_id int(11) unsigned NOT NULL auto_increment,
  sender_name varchar(30) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  login_id int(11) NOT NULL default '0',
  `text` blob NOT NULL,
  sender_id int(11) NOT NULL default '1',
  clan_id int(11) NOT NULL default '0',
  cat int(11) NOT NULL default '0',
  PRIMARY KEY  (message_id),
  KEY login_id (login_id),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM AUTO_INCREMENT=1204 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_messages`
--

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_mines`
--
DROP TABLE IF EXISTS QUANTUM_mines;
CREATE TABLE `QUANTUM_mines` (
  `mine_id` int(11) unsigned NOT NULL auto_increment,
  `login_id` int(11) NOT NULL default '0',
  `login_name` varchar(30) NOT NULL default '',
  `mine_type` tinyint(4) NOT NULL default '0',
  `clan_id` int(11) NOT NULL default '0',
  `v_clan_id` int(11) NOT NULL default '0',
  `cluster` int(11) NOT NULL default '0',
  `rel_target` tinyint(4) NOT NULL default '0',
  `location` int(11) NOT NULL default '0',
  `sequence` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`mine_id`),
  UNIQUE KEY `mine_id` (`mine_id`)
) Type=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `QUANTUM_mines`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_naps`
--
DROP TABLE IF EXISTS QUANTUM_naps;
CREATE TABLE `QUANTUM_naps` (
  `nap_id` int(11) unsigned NOT NULL auto_increment,
  `propose_id` int(11) NOT NULL default '0',
  `accept_id` tinyint(4) NOT NULL default '0',
  `prop_time` int(11) NOT NULL default '0',
  `start_time` int(11) NOT NULL default '0',
  `end_time` int(11) NOT NULL default '0',
  `activated` tinyint(4) NOT NULL default '0',
  `processed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`nap_id`),
  UNIQUE KEY `nap_id` (`nap_id`)
) Type=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `QUANTUM_naps`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_news`
--
DROP TABLE IF EXISTS QUANTUM_news;
CREATE TABLE `QUANTUM_news` (
  `news_id` int(11) unsigned NOT NULL auto_increment,
  `timestamp` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `headline` text NOT NULL,
  PRIMARY KEY  (`news_id`),
  KEY `news_id` (`news_id`),
  KEY `timestamp` (`timestamp`)
) Type=MyISAM AUTO_INCREMENT=125 ;

--
-- Dumping data for table `QUANTUM_news`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_news_maint`
--
DROP TABLE IF EXISTS QUANTUM_news_maint;
CREATE TABLE `QUANTUM_news_maint` (
  `news_id` int(11) unsigned NOT NULL auto_increment,
  `timestamp` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `headline` text NOT NULL,
  PRIMARY KEY  (`news_id`),
  KEY `news_id` (`news_id`),
  KEY `timestamp` (`timestamp`)
) Type=MyISAM AUTO_INCREMENT=52 ;

--
-- Dumping data for table `QUANTUM_news_maint`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_permissions`
--
DROP TABLE IF EXISTS QUANTUM_permissions;
CREATE TABLE `QUANTUM_permissions` (
  `login_id` int(11) unsigned NOT NULL default '0',
  `god` tinyint(1) unsigned NOT NULL default '0',
  `admin` tinyint(1) unsigned NOT NULL default '0',
  `forum` tinyint(1) unsigned NOT NULL default '0',
  `developer` tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY `login_id` (`login_id`)
) Type=MyISAM;

--
-- Dumping data for table `QUANTUM_permissions`
--

INSERT INTO `QUANTUM_permissions` VALUES (1, 0, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_planets`
--

CREATE TABLE QUANTUM_planets (
  planet_id int(11) unsigned NOT NULL auto_increment,
  planet_name varchar(30) NOT NULL default '',
  planet_type int(11) NOT NULL default '0',
  location int(11) NOT NULL default '0',
  owner_id int(11) NOT NULL default '0',
  owner_name varchar(30) NOT NULL default 'Nobody',
  fighters int(255) unsigned NOT NULL default '20',
  colon int(255) unsigned NOT NULL default '1000',
  fortress_level int(255) NOT NULL default '0',
  fighter_set int(255) NOT NULL default '0',
  cash int(255) NOT NULL default '0',
  tax_rate int(255) NOT NULL default '5',
  clan_id int(11) NOT NULL default '-1',
  metal int(255) unsigned NOT NULL default '0',
  fuel int(255) unsigned NOT NULL default '0',
  elect int(255) unsigned NOT NULL default '0',
  organ int(255) unsigned NOT NULL default '0',
  alloc_fight int(255) NOT NULL default '0',
  alloc_elect int(255) NOT NULL default '0',
  alloc_organ int(255) NOT NULL default '0',
  pass varchar(30) NOT NULL default '0',
  planet_img tinyint(255) default NULL,
  shield_gen tinyint(255) NOT NULL default '0',
  shield_charge int(255) NOT NULL default '0',
  launch_pad float NOT NULL default '0',
  missile int(255) NOT NULL default '0',
  tech int(255) NOT NULL default '0',
  research_fac tinyint(255) NOT NULL default '0',
  darkmatter int(255) NOT NULL default '0',
  wormhole_gen tinyint(4) NOT NULL default '0',
  wormhole int(255) NOT NULL default '0',
  barren tinyint(4) NOT NULL default '0',
  pop_limit int(255) NOT NULL default '0',
  unigen tinyint(4) NOT NULL default '0',
  super_hostile int(11) NOT NULL default '0',
  PRIMARY KEY  (planet_id),
  KEY planet_id (planet_id),
  KEY location (location),
  KEY owner_id (owner_id)
) ENGINE=MyISAM AUTO_INCREMENT=826 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_planets`
--

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_politics`
--
DROP TABLE IF EXISTS QUANTUM_politics;
CREATE TABLE `QUANTUM_politics` (
  `position_id` int(11) NOT NULL default '0',
  `position_name` varchar(30) NOT NULL default '',
  `login_id` int(11) NOT NULL default '0',
  `login_name` varchar(30) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`position_id`),
  UNIQUE KEY `position_id` (`position_id`)
) Type=MyISAM;

--
-- Dumping data for table `QUANTUM_politics`
--

INSERT INTO `QUANTUM_politics` VALUES (1, 'Monarch', 0, '', 0);
INSERT INTO `QUANTUM_politics` VALUES (2, 'Industry Senator', 0, '', 0);
INSERT INTO `QUANTUM_politics` VALUES (3, 'Military Senator', 0, '', 0);
INSERT INTO `QUANTUM_politics` VALUES (4, 'Defense Senator', 0, '', 0);
INSERT INTO `QUANTUM_politics` VALUES (5, 'Trade Senator', 0, '', 0);
INSERT INTO `QUANTUM_politics` VALUES (6, 'War Senator', 0, '', 0);
INSERT INTO `QUANTUM_politics` VALUES (7, 'Espionage Senator', 0, '', 0);
INSERT INTO `QUANTUM_politics` VALUES (8, 'Research Senator', 0, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_ports`
--

CREATE TABLE QUANTUM_ports (
  port_id int(11) unsigned NOT NULL auto_increment,
  location int(11) NOT NULL default '0',
  metal int(11) NOT NULL default '0',
  fuel int(11) NOT NULL default '0',
  elect int(11) NOT NULL default '0',
  organ int(11) NOT NULL default '0',
  metal_cap int(11) NOT NULL default '0',
  fuel_cap int(11) NOT NULL default '0',
  elect_cap int(11) NOT NULL default '0',
  organ_cap int(11) NOT NULL default '0',
  PRIMARY KEY  (port_id),
  KEY port_id (port_id),
  KEY location (location)
) ENGINE=MyISAM AUTO_INCREMENT=9319 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_ports`
--



-- --------------------------------------------------------

--
-- Table structure for table QUANTUM_resources;
--
DROP TABLE IF EXISTS QUANTUM_resources;
CREATE TABLE `QUANTUM_resources` (
  `resource_id` int(11) unsigned NOT NULL auto_increment,
  `resource_categ` int(11) unsigned NOT NULL default '1',
  `name` varchar(32) NOT NULL default 'Scrap (Unknown Material)',
  `sql_name` varchar(32) NOT NULL default 'scrap',
  `sell_price` int(11) unsigned NOT NULL default '0',
  `cost_price` int(11) unsigned NOT NULL default '0',
  `uv_percent` int(11) unsigned NOT NULL default '0',
  `uv_max_amount` int(11) unsigned NOT NULL default '0',
  `uv_min_amount` int(11) unsigned NOT NULL default '0',
  `rr_max_chance` int(11) unsigned NOT NULL default '0',
  `rr_min_chance` int(11) unsigned NOT NULL default '0',
  `description` text,
  UNIQUE KEY `resource_id` (`resource_id`)
) Type=MyISAM AUTO_INCREMENT=6 ;

--
-- Dumping data for table `QUANTUM_resources`
--

INSERT INTO `QUANTUM_resources` VALUES (1, 0, 'Metal', 'metal', 65, 80, 75, 20000, 0, 50, 25, 'Metal comprises of all metallic elements required for industry in the Modern Era. Given the wide range of metallic ore available from Asteroids (and their relatively simple extraction) planetary mining was long ago abandoned in favour of Asteroid mining. Most mining is performed by suitably equipped Freighters and more specialised mining vessels. Metal is an absolute necessity for a growing planet, essential to the production of Fighters, Drones and planet based installations.');
INSERT INTO `QUANTUM_resources` VALUES (2, 0, 'Fuel', 'fuel', 70, 90, 40, 100000, 2500, 50, 20, 'Fuel is a collective term for most combustible gases found in the Universe, including Hydrogen, Helium 3 and oddly enough, Water. Essential for both sustaining ship bound life and energy needs, and providing cheap (if archaic) propulsion for smaller Fighter and Drones.');
INSERT INTO `QUANTUM_resources` VALUES (3, 1, 'Electronics', 'elect', 1700, 2100, 0, 0, 0, 0, 0, 'Electronics are produced in Planet installations. Given the needs of Modern ships, Electronics are extremely expensive and their painstaking production uses a lot of Metal and Fuel resources. Essential for Fighters and Drones (which use highly advanced autonomous systems) and probably their most demanding use.');
INSERT INTO `QUANTUM_resources` VALUES (4, 1, 'Organics', 'organ', 50, 60, 0, 0, 0, 0, 0, 'Organics are cheap manufactured nutrient supplements. Many refuse to inquire into what they are made of, but without them Earth''s overpopulated nations would be unable to support their citizens.');
INSERT INTO `QUANTUM_resources` VALUES (5, 0, 'Darkmatter', 'darkmatter', 420, 520, 5, 5000, 100, 5, 0, 'Darkmatter is an elusive substance - very few physicists understand precisely what it is. Darkmatter is believed to have once been used by advanced Alien civilisations as the basis for a very powerful weapon. It is an officially banned material under Sol Authority Laws.');

--
-- Table structure for table `QUANTUM_resources`
--

--
-- Table structure for table `QUANTUM_ships`
--

CREATE TABLE QUANTUM_ships (
  ship_id int(11) unsigned NOT NULL auto_increment,
  ship_name varchar(30) NOT NULL default '',
  login_id int(11) NOT NULL default '0',
  login_name varchar(30) NOT NULL default 'Nobody',
  clan_id int(11) NOT NULL default '-1',
  location int(11) NOT NULL default '1',
  ship_categ tinyint(4) NOT NULL default '0',
  `type` varchar(150) NOT NULL default '0',
  shipclass int(11) NOT NULL default '0',
  class_name varchar(30) NOT NULL default '0',
  class_name_abbr varchar(10) NOT NULL default '0',
  shields int(11) NOT NULL default '0',
  max_shields int(11) NOT NULL default '0',
  fighters int(11) NOT NULL default '0',
  max_fighters int(11) NOT NULL default '0',
  cargo_bays int(11) NOT NULL default '0',
  metal int(11) unsigned NOT NULL default '0',
  fuel int(11) unsigned NOT NULL default '0',
  darkmatter int(11) unsigned NOT NULL default '0',
  elect int(11) unsigned NOT NULL default '0',
  organ int(11) unsigned NOT NULL default '0',
  colon int(11) unsigned NOT NULL default '0',
  mine_mode int(11) NOT NULL default '0',
  mine_rate_metal int(11) NOT NULL default '0',
  mine_rate_fuel int(11) NOT NULL default '0',
  mine_rate_darkmatter int(11) NOT NULL default '0',
  mine_rate int(11) NOT NULL default '0',
  move_turn_cost int(11) NOT NULL default '1',
  config text NOT NULL,
  size tinyint(4) NOT NULL default '1',
  disp_rank tinyint(4) NOT NULL default '0',
  upgrades int(11) NOT NULL default '0',
  point_value int(11) NOT NULL default '0',
  num_ot int(11) NOT NULL default '0',
  num_dt int(11) NOT NULL default '0',
  num_pc int(11) NOT NULL default '0',
  num_sa int(11) NOT NULL default '0',
  num_ew int(11) NOT NULL default '0',
  num_mi int(11) NOT NULL default '0',
  tech_bonus int(11) NOT NULL default '0',
  leech tinyint(11) NOT NULL default '0',
  missiles int(11) NOT NULL default '0',
  dmissiles int(11) NOT NULL default '0',
  bonus_attack tinyint(4) NOT NULL default '0',
  defend_fleet tinyint(4) NOT NULL default '0',
  shield_1 int(11) unsigned NOT NULL default '0',
  shield_2 int(11) unsigned NOT NULL default '0',
  shield_3 int(11) unsigned NOT NULL default '0',
  shield_4 int(11) unsigned NOT NULL default '0',
  shield_5 int(11) unsigned NOT NULL default '0',
  shield_charge int(11) unsigned NOT NULL default '0',
  max_charge int(11) unsigned NOT NULL default '0',
  shield_prts int(11) unsigned NOT NULL default '0',
  fleet_id int(11) unsigned NOT NULL default '0',
  `timestamp` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (ship_id),
  KEY ship_id (ship_id),
  KEY location (location),
  KEY fighters (fighters),
  KEY fleet_id (fleet_id),
  KEY login_id (login_id)
) ENGINE=MyISAM AUTO_INCREMENT=3720 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_ships`
--

INSERT INTO `QUANTUM_ships` VALUES (1, 'Ship Destroyed', 0, '', 0, 1, 0, '0', 0, '', 'SD', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `QUANTUM_ships` VALUES (2, 'Broby', 1, 'Admin', 0, 1, 10, 'Flagship', 12, 'Brobdingnagian', 'Brob', 1000, 1000, 32000, 32000, 3000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, 'oo:sv:tw:ot:dt:sw', 8, 0, 2, 150, 5, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 16000, 4, 2, 1113848058);

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_shipyards`
--

CREATE TABLE QUANTUM_shipyards (
  asyrd_id bigint(255) NOT NULL auto_increment,
  location bigint(255) NOT NULL default '0',
  move bigint(255) NOT NULL default '0',
  PRIMARY KEY  (asyrd_id)
) ENGINE=MyISAM AUTO_INCREMENT=622 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_shipyards`
--

--
-- Table structure for table QUANTUM_stars;
--
DROP TABLE IF EXISTS QUANTUM_stars;
CREATE TABLE `QUANTUM_stars` (
  `star_id` int(11) unsigned NOT NULL auto_increment,
  `star_name` varchar(30) NOT NULL default '',
  `sys_type` tinyint(4) NOT NULL default '0',
  `x_loc` int(11) NOT NULL default '0',
  `y_loc` int(11) NOT NULL default '0',
  `link_1` int(11) NOT NULL default '0',
  `link_2` int(11) NOT NULL default '0',
  `link_3` int(11) NOT NULL default '0',
  `link_4` int(11) NOT NULL default '0',
  `link_5` int(11) NOT NULL default '0',
  `link_6` int(11) NOT NULL default '0',
  `metal` int(11) unsigned NOT NULL default '0',
  `fuel` int(11) unsigned NOT NULL default '0',
  `darkmatter` int(11) unsigned NOT NULL default '0',
  `planetary_slots` int(11) NOT NULL default '0',
  `event_random` tinyint(4) NOT NULL default '0',
  `wormhole` int(11) NOT NULL default '0',
  `art_wh` int(11) NOT NULL default '0',
  `wh_login` int(11) NOT NULL default '0',
  `wh_clanid` int(11) NOT NULL default '0',
  `unigen` int(11) NOT NULL default '0',
  `t_a` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`star_id`),
  KEY `login_id` (`star_id`,`star_name`)
) Type=MyISAM AUTO_INCREMENT=1 ;

--
-- Dumping data for table `QUANTUM_stars`
--

INSERT INTO `QUANTUM_stars` VALUES (1, 'Sol', 0, 717, 52, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);


--
-- Table structure for table `QUANTUM_transfer_buffer`
--
DROP TABLE IF EXISTS QUANTUM_transfer_buffer;
CREATE TABLE `QUANTUM_transfer_buffer` (
  `buffer_id` int(11) unsigned NOT NULL auto_increment,
  `owner_id` int(11) unsigned NOT NULL default '0',
  `ship_id` int(11) unsigned NOT NULL default '0',
  `ship_categ` int(11) unsigned NOT NULL default '0',
  `class_name` varchar(32) NOT NULL default '0',
  `transferto_id` int(11) unsigned NOT NULL default '0',
  `timestamp` int(11) unsigned NOT NULL default '0',
  UNIQUE KEY `buffer_id` (`buffer_id`)
) Type=MyISAM AUTO_INCREMENT=3 ;

--
-- Dumping data for table `QUANTUM_transfer_buffer`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_upgrade_units`
--
DROP TABLE IF EXISTS QUANTUM_upgrade_units;
CREATE TABLE `QUANTUM_upgrade_units` (
  `upg_record` int(11) unsigned NOT NULL auto_increment,
  `ship_id` int(11) unsigned NOT NULL default '0',
  `fleet_id` int(11) unsigned NOT NULL default '0',
  `login_id` int(11) unsigned NOT NULL default '0',
  `clan_id` int(11) unsigned NOT NULL default '0',
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
  `shield_charger` int(11) unsigned NOT NULL default '0',
  `wormhole_stabiliser` int(11) unsigned NOT NULL default '0',
  `trans_drive` int(11) unsigned NOT NULL default '0',
  `ramscoop` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`upg_record`),
  UNIQUE KEY `upg_record` (`upg_record`)
) Type=MyISAM AUTO_INCREMENT=31 ;

--
-- Dumping data for table `QUANTUM_upgrade_units`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_used`
--
DROP TABLE IF EXISTS QUANTUM_used;
CREATE TABLE `QUANTUM_used` (
  `item_id` int(11) unsigned NOT NULL auto_increment,
  `item_name` varchar(30) NOT NULL default '',
  `item_code` varchar(30) NOT NULL default '',
  `item_type` int(11) unsigned NOT NULL default '0',
  `race` tinyint(4) NOT NULL default '0',
  `config` varchar(30) NOT NULL default '',
  `owner_id` int(11) unsigned NOT NULL default '0',
  `price` int(11) unsigned NOT NULL default '0',
  `timestamp` int(11) unsigned NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '0',
  `ship_id` int(11) unsigned NOT NULL default '0',
  `ship_type` int(11) unsigned NOT NULL default '0',
  `units` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`item_id`),
  UNIQUE KEY `item_id` (`item_id`)
) Type=MyISAM AUTO_INCREMENT=4 ;

--
-- Dumping data for table `QUANTUM_used`
--


-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_user_options`
--
CREATE TABLE QUANTUM_user_options (
  login_id int(11) unsigned NOT NULL default '0',
  color_scheme tinyint(4) NOT NULL default '1',
  theme varchar(250) NOT NULL default 'red_Tempest',
  news_back int(11) unsigned NOT NULL default '200',
  forum_back int(11) unsigned NOT NULL default '48',
  show_sigs tinyint(4) NOT NULL default '1',
  show_pics tinyint(4) NOT NULL default '1',
  show_minimap tinyint(4) NOT NULL default '1',
  allow_popups tinyint(4) NOT NULL default '1',
  show_config tinyint(1) NOT NULL default '0',
  show_aim tinyint(4) NOT NULL default '0',
  show_icq tinyint(4) NOT NULL default '0',
  planet_report tinyint(4) NOT NULL default '1',
  show_clan_ships tinyint(4) NOT NULL default '1',
  show_abbr_ship_class tinyint(4) NOT NULL default '1',
  show_rel_sym tinyint(4) NOT NULL default '1',
  enable_fleets tinyint(4) NOT NULL default '1',
  Status_Hidden tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (login_id),
  UNIQUE KEY login_id (login_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `QUANTUM_user_options`
--

INSERT INTO `QUANTUM_user_options` VALUES (1, 1, 'red_Tempest', 100, 36, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 1, 0);
INSERT INTO QUANTUM_user_options VALUES (3,0,'0',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

-- --------------------------------------------------------

--
-- Table structure for table `QUANTUM_users`
--
DROP TABLE IF EXISTS QUANTUM_users;
CREATE TABLE `QUANTUM_users` (
  `login_id` int(11) unsigned NOT NULL auto_increment,
  `login_name` varchar(120) NOT NULL default '',
  `race` tinyint(4) NOT NULL default '0',
  `joined_game` int(11) unsigned NOT NULL default '0',
  `game_login_count` int(11) unsigned NOT NULL default '0',
  `turns` int(11) unsigned NOT NULL default '40',
  `turns_run` int(11) unsigned NOT NULL default '0',
  `location` int(11) unsigned NOT NULL default '1',
  `ship_id` int(11) unsigned NOT NULL default '1',
  `cash` bigint(20) unsigned NOT NULL default '100000',
  `tech` int(11) unsigned NOT NULL default '0',
  `on_planet` int(11) unsigned NOT NULL default '0',
  `last_attack` int(11) unsigned NOT NULL default '1',
  `last_attack_by` varchar(30) NOT NULL default '',
  `ships_killed` int(11) unsigned NOT NULL default '0',
  `ships_lost` int(11) unsigned NOT NULL default '0',
  `ships_killed_points` int(11) unsigned NOT NULL default '0',
  `ships_lost_points` int(11) unsigned NOT NULL default '0',
  `show_enemy_ships` int(11) unsigned NOT NULL default '0',
  `show_user_ships` int(11) unsigned NOT NULL default '0',
  `genesis` int(11) unsigned NOT NULL default '0',
  `terra_imploder` int(11) unsigned NOT NULL default '0',
  `clan_id` int(11) unsigned NOT NULL default '0',
  `clan_sym` char(3) NOT NULL default '',
  `clan_sym_color` varchar(6) NOT NULL default '',
  `clan_leader` tinyint(4) NOT NULL default '0',
  `v_clan_id` int(11) unsigned NOT NULL default '0',
  `nap_id` int(4) NOT NULL default '0',
  `fighters_killed` int(11) unsigned NOT NULL default '0',
  `fighters_lost` int(11) unsigned NOT NULL default '0',
  `bounty` int(11) unsigned NOT NULL default '0',
  `score` int(11) unsigned NOT NULL default '0',
  `alien` int(11) unsigned NOT NULL default '0',
  `pirate` int(11) unsigned NOT NULL default '0',
  `alpha` int(11) unsigned NOT NULL default '0',
  `gamma` int(11) unsigned NOT NULL default '0',
  `delta` tinyint(4) NOT NULL default '0',
  `sn_effect` tinyint(4) NOT NULL default '0',
  `politics` int(11) unsigned NOT NULL default '0',
  `sig` varchar(150) NOT NULL default '',
  `last_request` int(11) unsigned NOT NULL default '0',
  `last_access_forum` int(11) unsigned NOT NULL default '0',
  `last_access_clan_forum` int(11) unsigned NOT NULL default '0',
  `last_access_news` int(11) unsigned NOT NULL default '0',
  `last_access_maint_news` int(11) unsigned NOT NULL default '0',
  `last_access_inbox` int(11) unsigned NOT NULL default '0',
  `banned_time` int(11) unsigned NOT NULL default '0',
  `banned_reason` blob NOT NULL,
  `one_brob` tinyint(11) unsigned NOT NULL default '0',
  `second_scatter` tinyint(4) NOT NULL default '0',
  `event` tinyint(4) NOT NULL default '0',
  `sideffect` tinyint(4) NOT NULL default '0',
  `grav_mine` int(11) unsigned NOT NULL default '0',
  `hornet_mine` int(11) unsigned NOT NULL default '0',
  `leech` int(11) unsigned NOT NULL default '0',
  `tachyon_comm` int(11) unsigned NOT NULL default '0',
  `bm_visit` int(11) unsigned NOT NULL default '0',
  `overdue` tinyint(4) NOT NULL default '0',
  `outlaw` tinyint(4) NOT NULL default '0',
  `ramscoop` tinyint(4) NOT NULL default '0',
  `email_address` text NOT NULL,
  `last_ss` bigint(255) NOT NULL,
  PRIMARY KEY  (`login_id`),
  KEY `login_id` (`login_id`),
  KEY `login_name` (`login_name`),
  KEY `ships_killed` (`ships_killed`),
  KEY `turns_run` (`turns_run`)
) Type=MyISAM AUTO_INCREMENT=14 ;

--
-- Dumping data for table `QUANTUM_users`
--

INSERT INTO QUANTUM_users VALUES (1,'The',2,1,270,1000,500,1,2,99999970000000,4294967295,0,0,'',0,0,0,0,0,0,1,1,0,'','',0,100001,0,0,0,0,0,0,0,1,1,0,1,0,'',1159125664,1159113495,1157582373,1159053270,1158955043,0,'',0,0,0,0,1000010,10,5000,5000,1008424,0,0,0,'',2,1159113504);
INSERT INTO QUANTUM_users VALUES (3,'The Aliens',-1,1,0,1000,274956874,0,0,100000000000000,4294967295,0,0,'',0,0,0,0,0,0,1,1,0,'','',0,100001,0,0,109910566,4294967295,0,0,0,1,1,0,1,0,'',4294967295,1135876548,1135875175,1134681194,1135878599,0,'',0,0,0,0,10,10,0,0,16,0,1,0,'',0,0);

