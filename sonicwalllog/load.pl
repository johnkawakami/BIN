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
