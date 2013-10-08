#! /usr/bin/python3
# run this first
#
# Scans a vpopmail/domains directory to catalog the maildirs, mailman lists
# ezmlm lists, and .qmail forwards.  It does this by guessing what each address
# does, so may not be 100% correct.  
#
# For example, a mailing list address that is a forward like this:
#   mylist@mydomain.com -> mylist@lists.mydomain.com
# will fail.  The script will think it's a forward, rather than a forward
# that points to a list; thus, mylist won't be added to the set of lists
# and won't be migrated.
# 
# The scan is only one step deep, so expect gotchas like that.
#
# The output is a file, /tmp/domains.pck, that is used in other related
# scripts.


vpopmail = "/mnt/backup/mail/"
domainsPck = "/tmp/domains.pck"

import os, sys, re, pickle
from os.path import join, getsize
from stat import *

# our data structures
domains = {}

# add the domains, excluding mailman 
for d in os.listdir( vpopmail ):
	if ( d != "mailman"):
		st = os.lstat( vpopmail + d ).st_mode
		if S_ISDIR(st):
			domains[ d ] = { 
				"path": vpopmail + d,
				"maildirs": [],
				"forwards": {},
				"mailmans": set(),
				"ezmlms": set()
			}

ezmlmre = re.compile( '^\.qmail-(.+?)-(digest-return-default|digest-return|accept-default|reject-default|return-default|accept|reject|digest|return)$' )
mailmanre = re.compile( '^\.qmail-(.+)-(admin|join|leave|request|subscribe|unsubscribe|confirm|bounces)$' )
qmailre = re.compile( '^\.qmail-(.+?)$' )
forwardre = re.compile( '^&(.*)$' )

for domain in domains:
	d = domains[domain]['path']
	print( "Directory " + d )
	for f in os.listdir( d ):
		print( "File " + f )
		st = os.lstat( d + "/" + f ).st_mode
		if S_ISDIR( st ):
			# If it's a directly, we'll check that it's a maildir.
			# If it's a maildir, it's a regular email address.
			maildirpath = d + "/" + f + "/Maildir"
			if ( os.path.exists( maildirpath ) ):
				mdst = os.lstat( maildirpath ).st_mode
				if S_ISDIR( mdst ):
					print( "Maildir " + f )
					domains[domain]["maildirs"].append(f)
		else:
			# It's a file, test by looking at the filename.
			# We're looking for .qmail-* which are used by 
			# mailman and ezmlm and forwards.
			if ( f == '.qmail-default' ):
				print( "Default" )
			elif ( f == "vpasswd" or f == ".vpasswd.lock" or f == "vpasswd.cdb" ):
				print( "Ignored" )
			# ezmlm test
			#   ezmlm addresses symlink to qmail files that pass the message
			#   through ezmlm programs.  The qmail files are in the directory
			#   ./<prefix>.  
			#   Ignore *-owner because that exists in both ezmlm and mailman
			#   addresses.
			elif ( ezmlmre.search(f) ):
				m = ezmlmre.search(f) 
				print( "Ezmlm " + m.group(1) )
				domains[domain]["ezmlms"].add( m.group(1) )
			# mailman test
			#  mailman addresses are .qmail files that pass messages into
			#  the mailmain program.
			#   Ignore *-owner because that exists in both ezmlm and mailman
			#   addresses.
			elif ( mailmanre.search(f) ):
				m = mailmanre.search(f)
				print( "Mailman " + m.group(1) )
				domains[domain]["mailmans"].add( m.group(1) )
			# catch all - will also catch mailman and ezmlm addresses
			elif ( f == ".dir-control" ):
				continue
			else:
				# capture after the first dash
				if (qmailre.search(f)):
					m = qmailre.search(f)
					qmailfile = m.group(1)
					# ignore -owner addresses -- these are for lists
					if ( qmailfile.endswith('-owner') ):
						print( "Owner address " + f )
					elif ( qmailfile in domains[domain]["ezmlms"] ):
						print( "Ezmlm list" );
					elif ( qmailfile in domains[domain]["mailmans"] ):
						print( "Mailman list" );
					else:
						#inspect the forward file to see if it's really a forward
						st = os.lstat( d + "/" + f ).st_mode
						if (S_ISLNK(st)):
							print("Some Symlink")
						else:
							fwd = open( d + "/" + f , "r")
							line = fwd.read()
							fwd.close()
							# the line must look like:
							# &some@emailaddress.com\n
							if (forwardre.search(line)):
								match = forwardre.search(line)
								domains[domain]["forwards"][qmailfile] = match.group(1)
								print( "Forward " + qmailfile )

print( "EZMLM lists" )
for d in domains:
	if ( domains[d]['ezmlms'] ):
		print( d + ": " )
		print( domains[d]['ezmlms'] )

print( "MAILMAN lists" )
for d in domains:
	if ( domains[d]['mailmans'] ):
		print( d + ": " )
		print( domains[d]['mailmans'] )

print( "FORWARDS" )
for d in domains:
	if ( domains[d]["forwards"] ):
		print( d + ": ")
		print( domains[d]["forwards"] )

print( "MAILDIRS" )
for d in domains:
	if ( domains[d]["maildirs"] ):
		print( d + ": ")
		print( domains[d]["maildirs"] )


picklestring = pickle.dumps(domains)
fh = open( domainsPck, "wb" )
fh.write( picklestring )
fh.close


