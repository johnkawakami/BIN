#!/usr/bin/python3

# Test script to dump lists of emails for ezmlm lists.         
# This isn't going to work for anything on your system.
# It's here as an example of how to use the ezmlmlist command.

domainsPck = "/tmp/domains.pck"
virtualDb = "/var/postfix/virtual.db"
mailBase = "/mnt/backup/mail"
archiveBase = "/mnt/backup/ezmlm-archives"
EZMLM2MBOX = "./ezmlm2mbox"
EZMLMLIST = "./ezmlmlist"

import pickle, subprocess, os

fh = open( domainsPck, "rb" )
domains = pickle.load( fh )
fh.close()

for domain in domains:
	for listName in domains[domain]["ezmlms"]:
		archive = ( "%s/%s/%s" % ( mailBase, domain, listName ) )
		print(archive)
		try:
			proc = subprocess.check_output( [ EZMLMLIST, archive ], stderr=subprocess.STDOUT, universal_newlines=True )
			print(proc)
		except:
			pass

