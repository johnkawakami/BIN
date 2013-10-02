#!/usr/bin/python3

# Test script that tries to archive all the lists on my server,
# This isn't going to work for anything on your system.
# It's here as an example of how to use the ezmlm2mbox command.

domainsPck = "/tmp/domains.pck"
virtualDb = "/var/postfix/virtual.db"
mailBase = "/mnt/backup/mail"
archiveBase = "/mnt/backup/ezmlm-archives"
EZMLM2MBOX = "./ezmlm2mbox"

import pickle, subprocess, os

fh = open( domainsPck, "rb" )
domains = pickle.load( fh )
fh.close()

for domain in domains:
	for listName in domains[domain]["ezmlms"]:
		archive = ( "%s/%s/%s/archive" % ( mailBase, domain, listName ) )
		mbox = ( "%s/%s/%s.mbox" % ( archiveBase, domain, listName ) )
		mboxPath = archiveBase + "/" + domain
		if (not os.path.exists(mboxPath)):
			subprocess.call( ["mkdir", mboxPath] )
		mfh = open( mbox, "wb" )
		print ( "%s > %s" % ( archive, mbox ) )
		mboxOutput = subprocess.Popen( [ EZMLM2MBOX, archive ], stdout=mfh )

