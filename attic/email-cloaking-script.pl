#! /usr/bin/perl

# Obsolete script to cloak an email address.
# Not really necessary anymore.

foreach my $email (@ARGV) {

        $email =~ s/@/ @ /;
        $email =~ s/\./ . /;

        @parts = split( ' ', $email );

        print "<script type='text/javascript'>\n";
        print "document.write('<a href=\"mailto:');\n";
        foreach my $word (@parts) {
                print "document.write('".$word."');\n";
        }
        print "document.write('\">');";
        foreach my $word (@parts) {
                print "document.write('".$word."');\n";
        }
        print "document.write('</a>');\n";
        print "</script>\n\n";
}
