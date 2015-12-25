#! /usr/bin/perl

use XML::RSS;
use WWW::Curl::Easy;

my $curl = WWW::Curl::Easy->new();
$curl->setopt(CURLOPT_HEADER,0);
open DEVNULL,">/dev/null";
$curl->setopt(CURLOPT_WRITEHEADER, DEVNULL );
$curl->setopt(CURLOPT_URL, $ARGV[0] );
my $response_body;
open (my $fileb, ">", \$response_body);
$curl->setopt(CURLOPT_WRITEDATA,$fileb);
my $retcode = $curl->perform;

my $rss = new XML::RSS;
$rss->parse($response_body);

foreach my $item (@{$rss->{'items'}}) 
{
    print $item->{'title'}."\n\n";
}

close DEVNULL;
