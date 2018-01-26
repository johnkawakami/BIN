<!--
This is a bit of code to add a floating box to your page that will let you switch stylesheets and fonts. I wrote it specifically to start trying out different "themes" and fonts, not to allow the end user to do these things. But the client will be using it to preview.

It's not fully parameterized, nor does it generate the HTML for the selects, so you'll need to hack the code.

I know, it feels incomplete, but my intention wasn't to make a product, but to solve my current problem.

First, you need to alter the LINK to the fonts on googleapis.com so it pulls down the fonts you want. Then edit the SELECT so it has the fonts. Then, edit the fonts array in the Javascript section to it matches up with the SELECT. Then, edit the selectors array in the Javascript section to match the selectors that are used to set fonts. I'm using the PURE library, so this looks a little weird. Whatever library you are using, find out which selectors set the font, and add them to the selectors array. Finally, set a font in the CSS files.

Next, modify the layouts array in the Javascript section to list all the theme CSS files. You should also explicitly link to one of these themes in your HTML HEAD.

The initialization code will try to match up the SELECT values with the CSS font setting and CSS stylesheets that are already active. I did this because i didn't want to explain mismatches to the client.

After the complex init code, the handlers are trivial. They change font settings and swap in theme files.

The attached file ends in PHP, but this is plain HTML code.
  -->
<form name="styleswitcher">
    <p>Style and Font Switcher</p>
    <p>
        <select name="layout">
            <option value="0">Layout A</option>
            <option value="1">Layout B</option>
            <option value="2">Layout C</option>
        </select>
    </p>
    <p>
        <select name="font">
            <option value="0">Alegreya</option>
            <option value="1">Alegreya Sans</option>
            <option value="2">Crimson Text</option>
            <option value="3">Fira Sans</option>
            <option value="4">Josefin Sans</option>
            <option value="5">Vollkorn</option>
        </select>
    </p>
</form>
<link href="https://fonts.googleapis.com/css?family=Alegreya|Alegreya+Sans|Crimson+Text|Fira+Sans|Josefin+Sans|Vollkorn" rel="stylesheet"> 
<style>
    form[name="styleswitcher"] {
        position: fixed;
        top: 200px;
        left: 10px;
        padding: 15px;
        border: 1px solid silver;
        border-radius: 3px;
        background-color: white;
        box-shadow: 3px 3px 5px #000;
    }
</style>
<script>
    // stylesheet plugin richardnillson.net
$.stylesheets = (function () {
    var stylesheets,
        add,
        clear;

    add = function (cssfile) {
        $('head').append('<link href="' + cssfile + '" rel="stylesheet" />');
        return stylesheets;
    };
    clear = function () {
        $('head link[rel=stylesheet]').remove();
        return stylesheets;
    };
    remove = function(cssfile) {
        $('head link[rel=stylesheet][href="'+cssfile+'"]').remove();
        return stylesheets;
    };
    has = function(cssfile) {
        var elm = $('head link[rel=stylesheet][href="'+cssfile+'"]');
        return (elm.length > 0);
    };

    return stylesheets = {
        add: add,
        clear: clear,
        remove: remove,
        has: has
    };
} ());

$(function() {
    var layouts = ['css/themes/a.css', 'css/themes/b.css', 'css/themes/c.css'];
    var fonts = ['Alegreya', 'Alegreya Sans', 'Crimson Text', 'Fira Sans', 'Josefin Sans', 'Vollkorn'];
    var fontSelectors = ['html', '.pure-g [class*="pure-u"]'];

    /* Change the SELECTS to point to the correct values.
     * The CSS theme, and the font, should be set in the HTML and CSS code.
     * No need to set it dynamically.  This script picks up the value.
     */
    var vals = $(fontSelectors[0]).css('font-family').split(',').map(function(e) {
        e = e.replace(/^"/, '');
        e = e.replace(/"$/, '');
        return e;
    });
    vals.reverse(); // reverse it so the first font is checked last.
    vals.forEach(function (element) {
        var index = fonts.indexOf(element);
        console.log(element, index);
        if (index > -1) {
            $('select[name="font"]').val(index);
        }
    });
    layouts.forEach(function (file, index) {
        if ($.stylesheets.has(file)) {
            $('select[name="layout"]').val(index);
        }
    });

    /*
     * Attach handlers to activate the selects.
     */
    $('form[name="styleswitcher"] select[name="layout"]').on('change', layoutChanged);
    $('form[name="styleswitcher"] select[name="font"]').on('change', fontChanged);

    function layoutChanged(evt) {
        var cssfile = layouts[parseInt(evt.target.value)];
        layouts.forEach($.stylesheets.remove);
        $.stylesheets.add(cssfile);
    }
    function fontChanged(evt) {
        var font = fonts[parseInt(evt.target.value)];
        fontSelectors.forEach(function (sel) {
            $(sel).css('font-family', font);
        });
    }
});
</script>
