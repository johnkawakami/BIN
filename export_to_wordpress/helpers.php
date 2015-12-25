/*
This is an example template file that will export articles from your bespoke CMS to and XML file that WordPress version 3.5 will import. There are several articles out there about this, and this is another one.

This code is a work in progress, and just this code alone won't export from your CMS. It's just an example of a functioning template.

The technique I used to create this template was to read up on WXR, then do an export of a single article. I copied that xml, and inserted the CMS content. Then, I exported our data, imported it into WordPress, found errors, fixed errors, and repeated the process until all the articles could be imported.

The template is attached, and some additional support code is below. This support code fixes some data that WP won't import.

The CMS uses TinyMCE which has inserted some _mce* attributes into the HTML. That gets stripped.

The template contains some values for users and categories that will need to be altered.
*/

function cdata($s) { return "<![CDATA[".clean($s)."]]>"; }
function clean($s) { 
	$o = preg_replace('/_mce_.+?".+?"/','',$s);
	$o = preg_replace('/mce_.+?".+?"/','',$o);
	$o = ltrim(rtrim($o));
	return $o;
}
if( !function_exists( 'xmlentities' ) ) {
    function xmlentities( $string ) {
        $not_in_list = "A-Z0-9a-z\s_-";
        return preg_replace_callback( "/[^{$not_in_list}]/" , 'get_xml_entity_at_index_0' , $string );
    }
    function get_xml_entity_at_index_0( $CHAR ) {
        if( !is_string( $CHAR[0] ) || ( strlen( $CHAR[0] ) > 1 ) ) {
            die( "function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type." );
        }
        switch( $CHAR[0] ) {
            case "'":    case '"':    case '&':    case '<':    case '>':
                return htmlspecialchars( $CHAR[0], ENT_QUOTES );
                break;
            default:
                return numeric_entity_4_char($CHAR[0]);
                break;
        }       
    }
    function numeric_entity_4_char( $char ) {
        return "&#".str_pad(ord($char), 3, '0', STR_PAD_LEFT).";";
    }   
}
