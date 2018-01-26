from gimpfu import *  
import os
from os.path import basename
from os.path import splitext
import glob
import shutil
import re
from HTMLParser import HTMLParser

layernames = ['caption', 'caption copy']
processed = 'renamed'

'''
This script incorporated GPL code from photolab fileresize.
This code is also GPL.

Saves the image with a new filename based on a serial number
and the text from the first line of the layer named "caption".
'''

class MLStripper(HTMLParser):
    def __init__(self):
        self.reset()
        self.fed = []
    def handle_data(self, d):
        self.fed.append(d)
    def get_data(self):
        return ''.join(self.fed)

def strip_tags(html):
    s = MLStripper()
    s.feed(html)
    return s.get_data()

def get_caption_first_line(i):
    for l in i.layers:
        if l.name in layernames:
            text = pdb.gimp_text_layer_get_text(l)
            if text:
                return text.split("\n")[0].strip()
            else:
                markup = pdb.gimp_text_layer_get_markup(l)
                return strip_tags(markup).split("\n")[0].strip()


def string_to_seo_filename(s):
    s = s.lower()
    s = re.sub(r'[,.!#@$%^&*\(\)]', '', s)
    s = re.sub(r'\s+', '-', s)
    return s


def process(img, filepathname, newpath, counter):
    filebasename = string_to_seo_filename(get_caption_first_line(img))
    filebasename = '{0:03d}-'.format(counter) + filebasename
    filename = newpath + '/' + filebasename + '.xcf'
    return filename


def clean_dir(d):
    if os.path.exists(d):
        shutil.rmtree(d)
    os.mkdir(d)


def plugin_main(dirname):
    counter = 1
    if os.path.exists(u''+dirname):
        #
        globpattern = u''+dirname + os.sep + '*.xcf'
        # return complete path name of files
        filepathnames = glob.glob(globpattern)
        #
        if filepathnames:
            messagebox = pdb.gimp_message_get_handler()
            pdb.gimp_message_set_handler(2)  # send messages in error console
            #
            newpathname = os.path.join(u''+dirname, processed+'/')
            #
            clean_dir(newpathname)
            #
            # Let start serious things
            pdb.gimp_message("File processing is starting, please wait...")
            for filepathname in filepathnames:
                try:
                    file = open(u''+filepathname, 'rb')
                except:
                    if os.path.exists(filepathname):
                        pdb.gimp_message("%s is a directory" % (filepathname))
                    else:
                        pdb.gimp_message("%s: Error" % (filepathname))
                    continue
                # Files processing
                try:
                    GIMPimage = pdb.gimp_xcf_load(0,
                                                  filepathname,
                                                  filepathname)
                except:
                    pdb.gimp_message("Invalid XCF image.")
                name = process(GIMPimage, filepathname, newpathname, counter)
                # copy the file to the new renamed file
                shutil.copyfile(filepathname, name)
                counter += 2

            # End of process
            pdb.gimp_message("End of the process")
            pdb.gimp_message_set_handler(messagebox)
        else:
            pdb.gimp_message("%s is empty" % (dirname))
    else:
        pdb.gimp_message("%s is not a directory" % (dirname))

register(
        "python_fu_riceball_rename_by_caption",
        "Saves files with names based on the first line of caption.",
        "Creates a folder named renamed, and writes renamed files into there.",
        "John Kawakami <johnk@riceball.com>",
        "GPL",
        "2016",
        "Rename files by caption",
        "",  # image types: blank means don't care but no image param
        [
            # Plugin parameter tuples: (type, name, description,
            # default, [extra])
            # Note these determine both the type of the parameter
            # and the GUI widget displayed.
            #
            # Note the underbar in the description tells what
            # letter is the shortcut key.
            #
            # Editable text boxes
            (PF_DIRNAME, "directory", "Directory", os.getcwd())
            ],
        [],
        plugin_main,
        menu="<Image>/Filters/Demos"

        # Optional registration parameters in PyGimp:
        # menu="<Image>/Filters")
        # # This is really the menupath, the menuitem is
        # above and can include the path
        # !!! But note no parameters are passed for certain menu paths.
        # Certain menu path prefixes are mangled by PyGimp:
        # <Image>/Languages means sibling to Script Fu and Python Fu items.
        # domain=("gimp20-python", gimp.locale_directory)
        # # internationalization
        )

main()
