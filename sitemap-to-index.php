<?php // vim:ai
/*
 * A snippet to produce an index of pages, based on a sitemap file
 * named sitemap.txt.
 *
 * This was used to create an index of pages on a legacy website
 * that had been archived.  The archive was produced with wget,
 * and contained folders with these pages.  Wget also pulled down
 * all the navigational pages, lists of articles, and so forth.
 *
 * To improve the findability of the pages, I created a sitemap,
 * and, later, this hack to display a single page with links to
 * all the articles.
 *
 * While all the other navigational pages still exist, this page
 * makes it easy for me to find the articles.  
 *
 * To accelerate the page rendering, I store the titles in a 
 * database.  To get the titles, the first time, we read each
 * page, and extract the contents of the title tag, and truncate
 * it a bit.
 */

$titledb = "/www/riceball.com/titles.sqlite";
class MyDB extends SQLite3 {
	function __construct() {
		global $titledb;
		$this->open($titledb);
	}
}

$db = new MyDB();
$db->enableExceptions(true);
try {
	$db->exec('CREATE TABLE titles (url TEXT PRIMARY KEY, title TEXT)');
} catch(Exception $e) {
	// do nothing
}

try{
	$urls = file('sitemap.txt');
	foreach($urls as $url) {
        // The database is a cache of titles.
		$title_stmt = $db->prepare('SELECT title FROM titles WHERE url=:url');
		$title_stmt->bindValue(':url', $url);
		$result = $title_stmt->execute();
		$row = $result->fetchArray();
        // If there's a row of results, we found the title.
		if ($row[0]) {
			$title = $row['title'];
		} else {
            // Otherwise, we did not find the title in the database,
            // and need to extract it from the HTML file.
            //
            // First, mangle the URL into a file path
			$path = rtrim(str_replace('http://riceball.com/d', dirname(__FILE__), $url));
			$file = file_get_contents($path);

            // Extract the title with a regexp.  DOMDocument couldn't load this file!
			preg_match('/<title>(.+)<\/title>/m', $file, $matches);

            // Trim the site name from the title.
			$title = substr($matches[1], 0, -32);

            // Insert it into the database.
			$insert_stmt = $db->prepare('INSERT INTO titles (title, url) VALUES (:title, :url)');
			$insert_stmt->bindValue(':url', $url);
			$insert_stmt->bindValue(':title', $title);
			$insert_stmt->execute();
		}
		echo "<a href='$url'>$title</a><br />";
	}
} catch(Exception $e) {
	echo $e;
}

