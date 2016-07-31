#!/usr/bin/python -u
'''
    ADOdb release upload script
'''

import getopt
import glob
import os
from os import path
import subprocess
import sys


# Directories and files to exclude from release tarballs
sf_files = "frs.sourceforge.net:/home/frs/project/adodb" \
           "/adodb-php5-only/adodb-{ver}-for-php5/"
sf_doc = "web.sourceforge.net:/home/project-web/adodb/htdocs/"
rsync_cmd = "rsync -vP --rsh ssh {opt} {src} {usr}@{dst}"

# Command-line options
options = "hfd"
long_options = ["help", "files", "doc"]


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
        -f | --files            Upload release files only
        -d | --doc              Upload documentation only
''' % (
        path.basename(__file__)
    )
#end usage()


def main():
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

    upload_files = True
    upload_doc = True

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-f", "--files"):
            upload_files = False

        elif opt in ("-d", "--doc"):
            upload_doc = False

    # Mandatory parameters
    username = args[0]

    try:
        release_path = args[1]
        os.chdir(release_path)
    except IndexError:
        release_path = os.getcwd()

    # Get the version number from the zip file to upload
    try:
        zipfile = glob.glob('*.zip')[0]
    except IndexError:
        print "ERROR: release zip file not found in '%s'    " % release_path
        sys.exit(1)
    version = zipfile[5:8]

    # Start upload process
    print "ADOdb release upload script"

    # Upload release files
    if upload_files:
        target = sf_files.format(ver=version)
        print
        print "Uploading release files..."
        print "  Target: " + target
        print
        subprocess.call(
            rsync_cmd.format(
                usr=username,
                opt="--exclude=docs",
                src=path.join(release_path, "*"),
                dst=target
            ),
            shell=True
        )

    # Upload documentation
    if upload_doc:
        print
        print "Uploading documentation..."
        print
        subprocess.call(
            rsync_cmd.format(
                usr=username,
                opt="",
                src=path.join(release_path, "docs", "*"),
                dst=sf_doc
            ),
            shell=True
        )

#end main()

if __name__ == "__main__":
    main()
