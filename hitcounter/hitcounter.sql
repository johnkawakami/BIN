CREATE TABLE `hitcounter` (
`id` int(11) NOT NULL auto_increment,
`count` int(11) default NULL,
`lastip` varchar(255) default NULL,
PRIMARY KEY (`id`),
KEY `lastip` (`lastip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
