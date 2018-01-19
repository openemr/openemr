#!/usr/bin/python -u
'''
    ADOdb release upload script
'''

from distutils.version import LooseVersion
import getopt
import glob
import os
from os import path
import re
import subprocess
import sys


# Directories and files to exclude from release tarballs
sf_files = "frs.sourceforge.net:/home/frs/project/adodb/"
rsync_cmd = "rsync -vP --rsh ssh {opt} {src} {usr}@{dst}"

# Command-line options
options = "hn"
long_options = ["help", "dry-run"]


def usage():
    print '''Usage: %s [options] username [release_path]

    This script will upload the files in the given directory (or the
    current one if unspecified) to Sourceforge.

    Parameters:
        username                Sourceforge user account
        release_path            Location of the release files to upload
                                (see buildrelease.py)

    Options:
        -h | --help             Show this usage message
        -n | --dry-run          Do not upload the files
''' % (
        path.basename(__file__)
    )
#end usage()


def call_rsync(usr, opt, src, dst):
    ''' Calls rsync to upload files with given parameters
        usr = ssh username
        opt = options
        src = source directory
        dst = target directory
    '''
    global dry_run

    command = rsync_cmd.format(usr=usr, opt=opt, src=src, dst=dst)

    if dry_run:
        print command
    else:
        subprocess.call(command, shell=True)


def get_release_version():
    ''' Get the version number from the zip file to upload
    '''
    try:
        zipfile = glob.glob('adodb-*.zip')[0]
    except IndexError:
        print "ERROR: release zip file not found in '%s'" % release_path
        sys.exit(1)

    try:
        version = re.search(
            "^adodb-([\d]+\.[\d]+\.[\d]+)\.zip$",
            zipfile
            ).group(1)
    except AttributeError:
        print "ERROR: unable to extract version number from '%s'" % zipfile
        print "       Only 3 groups of digits separated by periods are allowed"
        sys.exit(1)

    return version


def sourceforge_target_dir(version):
    ''' Returns the sourceforge target directory
        Base directory as defined in sf_files global variable, plus
        - if version >= 5.21: adodb-X.Y
        - for older versions: adodb-XYZ-for-php5
    '''
    # Keep only X.Y (discard patch number)
    short_version = version.rsplit('.', 1)[0]

    directory = 'adodb-php5-only/'
    if LooseVersion(version) >= LooseVersion('5.21'):
        directory += "adodb-" + short_version
    else:
        directory += "adodb-{}-for-php5".format(short_version.replace('.', ''))

    return directory


def process_command_line():
    ''' Retrieve command-line options and set global variables accordingly
    '''
    global upload_files, upload_doc, dry_run, username, release_path

    # Get command-line options
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError, err:
        print str(err)
        usage()
        sys.exit(2)

    if len(args) < 1:
        usage()
        print "ERROR: please specify the Sourceforge user and release_path"
        sys.exit(1)

    # Default values for flags
    dry_run = False

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-n", "--dry-run"):
            dry_run = True

    # Mandatory parameters
    username = args[0]

    # Change to release directory, current if not specified
    try:
        release_path = args[1]
        os.chdir(release_path)
    except IndexError:
        release_path = os.getcwd()


def upload_release_files():
    ''' Upload release files from source directory to SourceForge
    '''
    version = get_release_version()
    target = sf_files + sourceforge_target_dir(version)

    print
    print "Uploading release files..."
    print "  Source:", release_path
    print "  Target: " + target
    print
    call_rsync(
        username,
        "",
        path.join(release_path, "*"),
        target
    )


def main():
    process_command_line()

    # Start upload process
    print "ADOdb release upload script"

    upload_release_files()

#end main()

if __name__ == "__main__":
    main()
