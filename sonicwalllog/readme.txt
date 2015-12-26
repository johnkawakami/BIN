 This version of the script can be run as a cron job.

In the previous script, we lost some of the details of the protocol field. The protocol fields look like this: "tcp/http", "udp/dns" and so forth. It's the protocol and the application. Usually, this is identified by the port number, but I think the firewall does some packet inspection to identify it.

To save this data without consuming space, the appprotos table holds the names of the protocols, and in the logs table, we store a reference to appprotos. This reduces the appproto field to one byte (a tinyint). Since most traffic is "http" or "https" or "smtp", we gain a 4:1 compression.

The other bit of data to preserve is the msg field. That's a human-readable version of field m, which we store as a mediumint.

For both of these fields, we create records in the appprotos and messages table on an as-needed basis. When the script is executed, the tables are loaded into hashes. The log line values are encoded using these hashes. As new values are found, we insert the new values into the tables.

This has the nice side effect of keeping the tables small, so the key field can be kept as small as reasonably possible. (Also, I had a big bug in the previous entry -- the fields were signed values, but they should have been unsigned. Unsigned fields can store twice as many positive values.)

There are still some fields missing, but, at this time, I'm not going to save that data. I'm not even sure what reports I want, yet. Additionally, what I need right now is domain name resolution, so we can map from IP addresses to domain names.
