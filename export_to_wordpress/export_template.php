<?php 
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";
?>
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/1.1/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.1/"
>
<channel>
<language>en</language>
<wp:wxr_version>1.1</wp:wxr_version>
<wp:author><wp:author_id>7</wp:author_id><wp:author_login>pastnews</wp:author_login><wp:author_email>news@launionaflcio.org</wp:author_email><wp:author_display_name><?=cdata('pastnews');?></wp:author_display_name><wp:author_first_name><?=cdata('Past');?></wp:author_first_name><wp:author_last_name><?=cdata('News');?></wp:author_last_name></wp:author>
<wp:category><wp:term_id>20</wp:term_id><wp:category_nicename>news</wp:category_nicename><wp:category_parent/><wp:cat_name><?=cdata('News');?></wp:cat_name></wp:category>
<?
	while( $generic->next() ) { //begin generic loop ?><item>
<title><?=htmlspecialchars(strip_tags($generic->name))?></title>
<pubDate><?=date('Y-m-d',$generic->date)?></pubDate>
<dc:creator><?=cdata("pastnews");?></dc:creator>
<? // category needs to be mapped to the target categories ?>
<guid isPermaLink="false"></guid>
<description/>
<content:encoded><?
	if (hasText($generic->summary) && lacksText($generic->body)) {
		echo cdata($generic->summary_embed . $generic->summary);
	} else if (lacksText($generic->summary) && hasText($generic->body)) {
		echo cdata($generic->body_embed . $generic->body);
	} else if (hasText($generic->summary) && hasText($generic->body)) {
		echo cdata($generic->body_embed . $generic->body);
	}
?></content:encoded>
<excerpt:encoded><?=cdata('')?></excerpt:encoded>
<wp:post_date><?=date('Y-m-d h:m:s',$generic->date)?></wp:post_date>
<wp:comment_status>open</wp:comment_status>
<wp:post_name><?=$generic->handle?></wp:post_name>
<wp:status>publish</wp:status>
<wp:is_sticky>0</wp:is_sticky>
<wp:post_type>post</wp:post_type>
<wp:post_password/>
<category domain="category" nicename="news"><?=cdata('News')?></category>
</item><?php } //end generic loop ?></channel></rss>
