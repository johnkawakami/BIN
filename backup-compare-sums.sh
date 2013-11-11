#! /bin/bash
#
# Compare MD5 sums reports between two hosts.
# We run the summing script, backup-sums.sh on each host.
# Then we download the reports from the origin host into
# the backup host.  We use ssh -r and pull the reports
# into the same directory as the local reports (on the
# backup host).
#
# This script then drills down and sorts, then runs diff
# on each report.  The results are in $service/$domain/$service.$domain.diff
#
# To list these files, use find reports -name "*.diff" -exec ls -l {} |;


cd /mnt/backup/reports
scp -P 2222 -r slaptech.net:reports/* .

cd /mnt/backup/reports
host1="zanon.slaptech.net"
host2="johnk-desktop"

for service in *
do
	cd $service
	for domain in *
	do
		cd $domain
		sort $host1.md5 > $host1.sorted
		sort $host2.md5 > $host2.sorted
		diff $host1.sorted $host2.sorted > $service.$domain.diff
		cd ..
	done
	cd ..
done


