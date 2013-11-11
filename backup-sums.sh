#! /bin/bash
#
# Tool to produce lists of MD5 sums for files in subdirectories.
#
# Assumption is that $rootdir contains directories.  Each directory
# is a virtual host, or some other set of files for a domain.
# A report is produced for each directory.  The report is stored
# in a parallel directory structure.  By conventions we have
# directories like reports/www/domain.com, reports/mail/domain.com
# and the reports are named $hostname.md5
#
# The comparison script, backup-compare-sums.sh compares between
# two of these reports.  See that script for more info.
#

check_dir() 
{
	# $rootder is the directory to scan
	rootdir=$1
	# $reportrootdir is where the report is stored
	reportrootdir=$2

	# all reports have the name $domain/$hostname.md5

	cd "$rootdir"
	for domain in *
	do
		echo $rootdir/$domain
		mkdir -p "$reportrootdir/$domain"
		filename="$reportrootdir/$domain/$(hostname).md5"
		find $domain -type f -exec $MD5 $MD5OPT {} \; > "${filename}"
		#sort "${filename}.tmp" > "$filename"
		#rm "${filename}.tmp"
	done
}

host=$(hostname)

if [[ $host = "johnk-desktop" ]] ; then
	echo desk
	report="/mnt/backup/reports/"
	mkdir -p $report
	cd $report
	echo "start " $(date) > $host.log
	MD5="md5sum"
	MD5OPT="--tag"
	#check_dir /mnt/backup/www $report/logs
	#check_dir /mnt/backup/db $report/db
	check_dir /mnt/backup/www $report/www
	check_dir /mnt/backup/mail $report/mail
	cd $report
	echo "end " $(date) >> $host.log
elif [[ $host = "zanon.slaptech.net" ]] ; then
	echo zanon
	report="/home/johnk/reports/"
	mkdir -p $report
	cd $report
	echo "start " $(date) > reports/$host.log
	MD5="md5"
	MD5OPT=""
	check_dir /usr/local/www-domains/ $report/www/
	check_dir /usr/local/vpopmail/domains/ $report/mail
	cd $report
	echo "end " $(date) >> $host.log
fi

