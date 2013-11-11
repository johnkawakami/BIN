bin
===

A collection of random scripts that search engines might find.

*config.py*: configuration for migrating from ezmlm to mailman + postfix

*ezmlm2mbox*: converts an ezmlm archive into an mbox file, to migrate to mailman.

*ezmlmlist*: dumps subscribers from an ezmlm list, similar to ezmlm-list in the ezmlm tools

*ezmlm2mailman*: duplicates an ezmlm list to Mailman, including subscribers and archives.

*markdown.html*: a form that renders markdown, and has autosave if your browser supports localStorage.

*scan.py*: a prep tool that scans an qmail/vpopmail/ezmlm/mailman directory and then produces a data structure cataloging the contents.

*backup-sums.sh*: reports on checksums of many files

*backup-compare-sums.sh*: diffs all the above reports

The bad news is, these sums take too long to calculate, so the whole thing is virtually useless.
I need something that runs in a few minutes, not hours.  Back to the drawing board.
It works well for small sets of files, though.
