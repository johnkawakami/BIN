
News?
Los Angeles News"; ?>
johnk

    Contact
    My account
    Create content
    Recent posts
    Administer
    Log out

Syndicate
Syndicate content
Home › Blogs › johnk's blog
Creating a Firewall Log Analysis Tool for a SonicWall DSL Router, Part 2

    View Edit Outline Track 

Thu, 07/09/2009 - 10:00 — johnk

In the last entry, log lines were being "compressed" by placing them into a database table. There were a few bugs in that code that have been fixed, and features added to the new script, below, that save us from losing some log data. Explanation after the code:

File: load

#! /usr/bin/perl
# vim:ts=4:sw=4:ai:

use Date::Parse;
use DBI;
use Socket;

$dsn = "DBI:mysql:database=firewall;host=localhost";
$dbh = DBI->connect( $dsn, 'firewall', '' );

$path = '/home/johnk/Sonicwall';

rename "$path/log", "$path/log.tmp";
system('/etc/init.d/rsyslog restart');

my %messages = ();
my %protos = ();

## load app protos from the db.  the ids don't necessarily match the port numbers.
$sql = "SELECT id,name FROM appprotos";
$sth = $dbh->prepare($sql);
$sth->execute();
while( @row = $sth->fetchrow_array )
{
        $protos{$row[1]} = $row[0];
}

open LOG,"<$path/log.tmp";
while (my $line = <LOG>)
{
        chomp $line;

        my @parts = split /\s+(\w+)=/, $line;
        shift @parts;
        %hash = @parts;

        my ($ip, $srcport, $srcint) = split /:/,$hash{src};
        $hash{src} = unpack('l',inet_aton($ip));

        my ($ip, $dstport, $dstint) = split /:/,$hash{dst};
        $hash{dst} = unpack('l',inet_aton($ip));

        $hash{'time'} = str2time(substr($hash{'time'},1,-1));

        my ($netproto, $appproto) = split /\//,$hash{proto};

        ## Substitute proto with the numeric code.  If it doesn't exist, then
        ## add it to the list, and then do the substitution.
        if ($protos{$appproto})
        {
                $appproto = $protos{$appproto};
        }
        else
        {
                $sql = "INSERT INTO appprotos (`name`) VALUES ('$appproto')";
                $sth = $dbh->prepare($sql);
                $result = $sth->execute();
                print "$sql\n" if (! $result);
                $id = $sth->{'mysql_insertid'};
                $protos{$appproto} = $id;
                $appproto = $id;
        }

        $sent = $hash{sent} + 0;
        $recd = $hash{recd} + 0;

        $sql = "INSERT INTO logs VALUES ($hash{time},$hash{pri},$hash{m},$hash{src},$srcport,'$srcint',$hash{dst},$dstport,'$dstint','$netproto',0,$sent,$recd)";
        $sth = $dbh->prepare($sql);
        if (! $sth->execute() )
        {
                print $line;
                print "\n";
                print $sql;
                print "\n";
                exit;
        }

        ## make a memo about the message
        $messages{$hash{m}} = $hash{msg};
}
close LOG;

## Add all the gathered msg messages into the messages table.
foreach my $key (keys %messages)
{
        $msg = substr($messages{$key}, 1, -1);
        $sql = "INSERT IGNORE INTO messages (id,name) VALUES ($key,'$msg')";
        $sth = $dbh->prepare($sql);
        $sth->execute();
}

unlink "$path/log.tmp";

Table definitions for the above:

--
-- Table structure for table `appprotos`
--

CREATE TABLE IF NOT EXISTS `appprotos` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(255) collate ascii_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=ascii COLLATE=ascii_bin AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `datetime` int(11) unsigned NOT NULL,
  `pri` smallint(2) unsigned NOT NULL,
  `m` smallint(3) unsigned NOT NULL,
  `src` int(11) unsigned NOT NULL,
  `srcport` mediumint(5) unsigned NOT NULL,
  `srcint` enum('','WAN','LAN','OPT') collate ascii_bin NOT NULL,
  `dst` int(11) unsigned NOT NULL,
  `dstport` mediumint(5) unsigned NOT NULL,
  `dstint` enum('','WAN','LAN','OPT') collate ascii_bin NOT NULL,
  `proto` enum('tcp','udp','icmp') collate ascii_bin NOT NULL,
  `appproto` smallint(3) unsigned default NULL,
  `sent` int(11) unsigned NOT NULL,
  `recd` int(11) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=ascii COLLATE=ascii_bin;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` smallint(3) NOT NULL,
  `name` varchar(255) collate ascii_bin NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii COLLATE=ascii_bin;
