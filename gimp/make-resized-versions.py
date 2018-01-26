from gimpfu import *  
import os
from os.path import basename
from os.path import splitext
import glob
import shutil

layernames = ['caption','caption copy','logo','logo copy']
thumbsize = 300
processed = 'processed'

'''
What this tool does is take a directory full of XCF files, and produces a directory with thumbnails, decorated full-size images, and unadorned full-size images. The adornments are a logo and caption, which are in layers named "logo" and "caption".
'''


'''
This script incorporated GPL code from photolab fileresize.
This code is also GPL.

This script generates three outputs from a folder of XCF files.

Exports the following:
 - full size, adorned jpegs
 - full size, unadorned jpegs
 - thumbnail, unadorned jpegs

The unadorned files have the layernames (listed above) hidden
before the JPEG is exported.
'''

def hide(i):
  for l in i.layers:
    if l.name in layernames:
      l.visible = False

def process( img, filepathname, decoratedpath, plainpath, scaledpath ):
  filebasename = splitext(basename(filepathname))[0]
  filename = decoratedpath + '/' + filebasename + '.jpg'
  newimg = gimp.Image(img.width, img.height, RGB)
  layer = pdb.gimp_layer_new_from_visible(img, newimg, "Composite")
  pdb.file_jpeg_save(newimg, layer, filename, filename, .95, 0, 1, 1, '', 0, 0, 0, 0)
  
  hide(img)
  filename = plainpath + '/' + filebasename + '.jpg'
  newimg = gimp.Image(img.width, img.height, RGB)
  layer = pdb.gimp_layer_new_from_visible(img, newimg, "Composite")
  pdb.file_jpeg_save(newimg, layer, filename, filename, .95, 0, 1, 1, '', 0, 0, 0, 0)
  
  img.scale( 300, 300 )
  filename = scaledpath + '/' + filebasename + '.jpg'
  newimg = gimp.Image(img.width, img.height, RGB)
  layer = pdb.gimp_layer_new_from_visible(img, newimg, "Composite")
  pdb.file_jpeg_save( img, layer, filename, filename, .95, 0, 1, 1, '', 0, 0, 0, 0 )


def clean_dir(d):
  if os.path.exists( d ):
    shutil.rmtree( d )
  os.mkdir( d )


def plugin_main(dirname):
  if os.path.exists( u''+dirname ):
    #
    globpattern = u''+dirname + os.sep + '*.xcf'
    filepathnames = glob.glob( globpattern ) # return complete path name of files
    #
    if filepathnames:
      messagebox = pdb.gimp_message_get_handler( ) 
      pdb.gimp_message_set_handler( 2 ) # send messages in error console
      #
      dirscaledpathname = os.path.join( u''+dirname, processed+'/thumb-'+str(thumbsize) )
      decoratedpathname = os.path.join( u''+dirname, processed+'/decorated' )
      plainpathname = os.path.join( u''+dirname, processed+'/plain' )
      #
      clean_dir( dirscaledpathname )
      clean_dir( decoratedpathname )
      clean_dir( plainpathname )
      #
      # Let start serious things  
      pdb.gimp_message( "File processing is starting, please wait..." )
      for filepathname in filepathnames:
        try:
          file = open( u''+filepathname, 'rb' )
        except:
          if os.path.exists( filepathname ):
            pdb.gimp_message( "%s is a directory" %(filepathname) )
          else:
            pdb.gimp_message( "%s: Error" %(filepathname) )
          continue    
        # Files processing
        try:
          GIMPimage= pdb.gimp_xcf_load( 0, filepathname, filepathname)
        except:
          pdb.gimp_message( "Invalid XCF image." )
          
        process( GIMPimage, filepathname, decoratedpathname, plainpathname, dirscaledpathname )
        
      # End of process         
      pdb.gimp_message( "End of the process" )        
      pdb.gimp_message_set_handler( messagebox )
    else:
      pdb.gimp_message( "%s is empty" %(dirname) )      
  else:
    pdb.gimp_message( "%s is not a directory" %(dirname) )

register(
    "python_fu_riceball_resize",
    "Exports thumbs and unadored images.",
    "Creates a folder named processed, and writes files into there.",
    "John Kawakami <johnk@riceball.com>",
    "GPL",
    "2016",
    "Export Thumbs and Unadorned",
    "", # image types: blank means don't care but no image param
    [ 
      # Plugin parameter tuples: (type, name, description, default, [extra])
      # Note these determine both the type of the parameter and the GUI widget displayed.
      # Note the underbar in the description tells what letter is the shortcut key.
      #
      # Editable text boxes
      (PF_DIRNAME, "directory", "Directory", os.getcwd() )
    ],
    [],
    plugin_main,
    menu="<Image>/Filters/Demos"

    # Optional registration parameters in PyGimp:
    # menu="<Image>/Filters")   # This is really the menupath, the menuitem is above and can include the path
    # !!! But note no parameters are passed for certain menu paths.
    # Certain menu path prefixes are mangled by PyGimp: <Image>/Languages means sibling to Script Fu and Python Fu items.
    # domain=("gimp20-python", gimp.locale_directory) # internationalization
    )

main()
