<?php

function show_instructions() {
echo '
<html><body><h1>CSV to Atom for Blogger</h1>
<p>Paste a table of titles and URLs from  LibreOffice Calc,
and use it to produce an Atom file that can be imported into Blogger to
produce multiple blog posts that will link to these pages.
 Useful for queuing up work to do. To use this,
paste the data into the text box below. Submit it, and then save the output.
In Blogger, import, but do not have it publish the articles.
</p>
<p>Expected format is:</p>
<pre>
title[tab]url
</pre>
<form method="post">
<textarea name="text" rows=20 cols=80></textarea>
<br />
<input type="submit" />
</form>
</body></html>
';
}

$post_template = '<entry>
    <id>{{id}}</id>
    <title type="html">{{title}}</title>
    <published>{{date}}</published>
    <category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/blogger/2008/kind#post"/>
    <content type="html">{{content}}</content>
</entry>
';

$feed_template = '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet href="https://www.blogger.com/styles/atom.css" type="text/css"?>
<feed xmlns="http://www.w3.org/2005/Atom"> 
    <id>noid</id>
    <title>notitle</title>
    <generator>Blogger</generator>
    <author>
        <name>None</name>
        <email>noreply@blogger.com</email>
    </author>
{{entries}}
</feed>
';

function merge_template($template, $context) {
    $newcontext = [];
    foreach($context as $name=>$value) {
        $newcontext['/{{'.$name.'}}/'] = $value;    
    }
    return preg_replace(array_keys($newcontext), array_values($newcontext), $template);
}

$post = filter_input_array( INPUT_POST, [
    'text' => FILTER_SANITIZE_STRING
]);

if (null == $post) {
    show_instructions();
    exit();
}

if (array_key_exists('text',$post)) {
    emit_atom($post['text']);
}

function emit_atom($text) {
    global $post_template, $feed_template;
    $data = [];
    foreach(explode("\n",$text) as $line) {
        $parts = explode("\t", rtrim($line));
        if ($parts[0]!=null and $parts[1]!=null) {
            $data[] = ['title'=>$parts[0], 'url'=>$parts[1]];
        }
    }
    foreach($data as $datum) {
        $title = $datum['title'];
        $content = '<a href="'.$datum['url'].'">'.$title.'</a>';
        $content = htmlspecialchars($content);
        $date = date(DATE_ATOM);
        $id = 'tag:ebay.com,2017:ebay-'.substr($datum['url'], 20);
        $output[] = merge_template($post_template, ['id'=>$id, 'date'=>$date, 'title'=>$title, 'content'=>$content]);
    }

    $xml = merge_template($feed_template, ['entries'=>join('', $output)]);

    header('Content-Type: application/atom+xml');
    header('Content-Disposition: attachment; filename="atom.xml"');
    header('Content-Length: '.strlen($xml));
    echo $xml;
}
