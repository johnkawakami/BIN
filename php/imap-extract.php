<?php
// vim:ts=4:sw=4:ai
//
// This was a utility library for a program that downloaded email
// from an IMAP server, turned it into a web page, and then
// mailed the web page to me.

class IMAPAccount {
	var $index;
	var $box;
	var $emails;
	function IMAPAccount()
	{
		$this->index = null;
	}
	function connect( $server, $folder, $account, $password )
	{
		$this->box = imap_open("{$server}$folder",$account,$password) or die("Cannot connect to $server: " . imap_last_error());
		return true;
	}
	function load_new()
	{
		$this->emails = imap_search($this->box, 'UNSEEN');
		return sizeof($this->emails);
	}
	function rewind()
	{
		$this->index = NULL;
	}
	function next()
	{
		if ($this->index === NULL) {
			$this->index = 0;
		} else {
			$this->index++;
		}
		if (!is_array($this->emails))
			return false;
		if ($this->index >= count($this->emails))
			return false;

		$email_number = $this->email_number = $this->emails[$this->index];
		$overview = imap_fetch_overview($this->box, $email_number, 0);
		$this->seen = $overview[0]->seen;
		$this->subject = $overview[0]->subject;
		$this->from = $overview[0]->from;
		$this->date = $overview[0]->date;
		$this->uid = $overview[0]->uid;
		$this->size = $overview[0]->size;
		$this->header = $overview[0];
		#echo $email_number . '<br>';
		#echo $this->subject . '<br>';
		#echo $this->size . '<br>';
		#echo "\r\n";

        $this->structure = null;

		return true;
	}
	function get_message_html_preferred()
	{
		$email_number = $this->email_number;
		$st = $this->fetch_structure();
		# print_r($st);
		if ($st->subtype=='PLAIN') {
            echo "PLAIN: $email_number $this->subject<br>";
			$this->body = imap_qprint(imap_fetchbody($this->box,$email_number,1));
			$this->body = t2h($this->body);
		} else if ($st->subtype=='HTML') {
            echo "HTML: $email_number $this->subject<br>";
			$this->body = strip_all(imap_qprint(imap_fetchbody($this->box,$email_number,1)));
		} else if ($st->subtype=='ALTERNATIVE') {
            echo "ALTERNATIVE: $email_number $this->subject<br>";
            if ($st->parts[0]->encoding==0)
                $this->body = t2h(imap_fetchbody($this->box,$email_number,'1'));
            else
                $this->body = t2h(imap_qprint(imap_fetchbody($this->box,$email_number,'1')));
		} else if ($st->subtype=='MIXED') {
            echo "MIXED: $email_number $this->subject<br>";
			$this->body = $this->find_message_in_mixed();
		} else {
            echo "** NOT DOWNLOADED: $st->subtype: $email_number $this->subject<br>";
        }
		return $this->body;
	}
    function find_message_in_mixed()
    {
        $this->fetch_structure();
        #print_r($this->structure);
        #echo "<hr>";
        #echo $this->find_message_in_mixed_r($this->structure->parts);
        #echo "<hr>";
        $path_to_message = $this->find_message_in_mixed_r($this->structure->parts);
        return t2h(imap_qprint(imap_fetchbody($this->box,$this->email_number,$path_to_message)));
    }
    function find_message_in_mixed_r($parts)
    {
        for($i=0;$i<count($parts);$i++)
        {
            if ($parts[$i]->subtype=='PLAIN')
                return $i+1;
            if ($parts[$i]->subtype=='ALTERNATIVE')
                return $i+1 . '.' . $this->find_message_in_mixed_r($parts[$i]->parts);
            if ($parts[$i]->subtype=='MIXED')
                return $i+1 . '.' . $this->find_message_in_mixed_r($parts[$i]->parts);
        }
    }
	function close()
	{
		imap_close($this->box);
	}
	function mark_as_read()
	{
		return;
		$email_number = $this->email_number;
		imap_setflag_full($this->box,$email_number,'\Seen');
	}
	function fetch_structure()
	{
    if ($this->structure) return $this->structure;
		$email_number = $this->email_number;
		$this->structure = imap_fetchstructure($this->box,$email_number);
    return $this->structure;
	}
}

class DigestMessage {
	var $messages;
	var $titles;
	function DigestMessage()
	{
		$this->messages = array();
		$this->titles = array();
	}
	function add($title, $body)
	{
		$this->titles[] = $title;
		$this->messages[] = $body;
	}
	function get_html()
	{
		$o = $index = '';
		for($i = 0; $i < count($this->titles); $i++)
		{
			$title = $this->titles[$i];
			$message = $this->messages[$i];
			$index .= "<a href=\"#$i\">$title</a><br>\r\n";
			$o .= "<hr><a name=\"$i\">$title</a><br>\r\n";
			$o .= "$message\r\n";
		}
		return $index.$o;
	}
}

function strip_all($t)
{
    $allowed = '<a><br><b><p><u><i><ul><li><ol><table><tr><td><th><dd><dt>';
    return strip_tags(strip_css(strip_html_head(strip_doctype($t))),$allowed);
}
function strip_css($html)
{
    return preg_replace('#<style.+?</style>#si','',$html);
}
function strip_html_head($html)
{
    return preg_replace('#<head>.+</head>#si','',$html);
}
function strip_doctype($html)
{
    return preg_replace('/<!DOCTYPE.+?>/','',$html);
}

## http://www.totallyphp.co.uk/code/convert_links_into_clickable_hyperlinks.htm
function make_clickable_links($text) {
  # protect the angle brackets that people put around links
  # i.e. <http://yahoo.com>
  # wtf?
  $text = eregi_replace('&lt;(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=;,]+)&gt;',
    '&lt;\\1>', $text);
  $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=;,]+)',
    '<a href="\\1"> LINK </a>', $text);
  $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)',
    '\\1<a href="http://\\2">\\2</a>', $text);
  $text = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})',
    '<a href="mailto:\\1">\\1</a>', $text);
  return $text;
}

function text_to_html($text, $useYahooLineEndings = false) {
  # convert to array
  $lines = preg_split('/(\r\n|\n)/',$text);
  # pad with blanks at start and end
  array_unshift($lines,"\n");
  array_push($lines,"\n");
  $linecount = count($lines);

  # the tags array contains descriptions of each line
  $tags = array();
  for($i=0;$i<$linecount;$i++)
    $tags[$i] = array();

  # wipe out blank lines
  for($i=0;$i<$linecount;$i++)
    $lines[$i] = preg_replace('/^\s+/','',$lines[$i]);

  # detect long lines
  for($i=0;$i<$linecount;$i++)
    $tags[$i]['long'] = (strlen($lines[$i]) > 50) ? 1 : 0;

  # detect quoted lines
  for($i=0;$i<$linecount;$i++)
    $tags[$i]['quote'] = (preg_match('/^(>|&gt;)/',$lines[$i]));

  # detect blank lines
  for($i=0;$i<$linecount;$i++)
    $tags[$i]['blank'] = (preg_match('/^$/',$lines[$i]));

  # detect lines that are supposed to be horizontal bars
  for($i=0;$i<$linecount;$i++)
  {
    $start = substr($lines[$i],0,1);
    if ( $start && !strpos('.*+?/[({',$start) )
    {
      if (preg_match("/^$start+$/",$lines[$i]))
        $tags[$i]['hr'] = 1;
    }
  }

  # mark short lines that follow a long line as long
  for($i=1;$i<$linecount;$i++)
  {
    if ($tags[$i-1]['long'] == 1  && $tags[$i]['long'] == 0)
      $tags[$i]['long'] = 1;
    if ($tags[$i]['blank']) 
      $tags[$i]['long'] = 0;
  }
  
  # mark first long lines (after blanks) as start paragaphs 
  for($i=1;$i<$linecount;$i++)
  {
    if ($tags[$i-1]['blank'] == 1  && $tags[$i]['long'] == 1)
      $tags[$i]['startp'] = 1;
  }
  # mark last long lines (before blanks) as end paragaphs 
  for($i=0;$i<$linecount-1;$i++)
  {
    if ($tags[$i]['long'] == 1  && $tags[$i+1]['long'] == 0)
      $tags[$i]['endp'] = 1;
  }

  # now sweep through the arrays and build the output
  # ignore first and last lines we added to the ends
  for($i=1;$i<$linecount-1;$i++)
  {
    $addbreak = false;
    if ($tags[$i]['startp'])
      $o .= '<p>';
    $o .= $lines[$i]."\n";
    if ($tags[$i]['hr'])
      $addbreak = true;
    if ($tags[$i]['quote'])
      $addbreak = true;
    if ($tags[$i]['long']==0 && $tags[$i]['blank']==0)
      $addbreak = true;
    if ($addbreak)
      $o .= '<br>';
    if ($tags[$i]['endp'])
      $o .= '</p>';
  }

  return $o;
}

function t2h($text)
{
  return make_clickable_links(text_to_html(htmlspecialchars($text)));
}
