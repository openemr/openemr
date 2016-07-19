#!/usr/bin/python -u
'''
    ADOdb version update script

    Updates the version number, and release date in all php, txt and htm files
'''

from datetime import date
import getopt
import os
from os import path
import re
import subprocess
import sys


# ADOdb version validation regex
_version_dev = "dev"
_version_regex = "[Vv]?[0-9]\.[0-9]+(%s|[a-z]|\.[0-9])?" % _version_dev
_release_date_regex = "[0-9?]+-.*-[0-9]+"
_changelog_file = "docs/changelog.md"

_tag_prefix = "v"


# Command-line options
options = "hct"
long_options = ["help", "commit", "tag"]


def usage():
    print '''Usage: %s version

    Parameters:
        version                 ADOdb version, format: [v]X.YY[a-z|dev]

    Options:
        -c | --commit           Automatically commit the changes
        -t | --tag              Create a tag for the new release
        -h | --help             Show this usage message
''' % (
        path.basename(__file__)
    )
#end usage()


def version_check(version):
    ''' Checks that the given version is valid, exits with error if not.
        Returns the version without the "v" prefix
    '''
    if not re.search("^%s$" % _version_regex, version):
        usage()
        print "ERROR: invalid version ! \n"
        sys.exit(1)

    return version.lstrip("Vv")


def release_date(version):
    ''' Returns the release date in DD-MMM-YYYY format
        For development releases, DD-MMM will be ??-???
    '''
    # Development release
    if version.endswith(_version_dev):
        date_format = "??-???-%Y"
    else:
        date_format = "%d-%b-%Y"

    # Define release date
    return date.today().strftime(date_format)


def sed_script(version):
    ''' Builds sed script to update version information in source files
    '''

    # Version number and release date
    script = "s/%s\s+%s/v%s  %s/\n" % (
        _version_regex,
        _release_date_regex,
        version,
        release_date(version)
    )

    return script


def sed_filelist():
    ''' Build list of files to update
    '''
    def sed_filter(name):
        return name.lower().endswith((".php", ".htm", ".txt"))

    dirlist = []
    for root, dirs, files in os.walk(".", topdown=True):
        for name in filter(sed_filter, files):
            dirlist.append(path.join(root, name))

    return dirlist


def tag_name(version):
    return _tag_prefix + version


def tag_check(version):
    ''' Checks if the tag for the specified version exists in the repository
        by attempting to check it out
        Throws exception if not
    '''
    subprocess.check_call(
        "git checkout --quiet " + tag_name(version),
        stderr=subprocess.PIPE,
        shell=True)
    print "Tag '%s' already exists" % tag_name(version)


def tag_delete(version):
    ''' Deletes the specified tag
    '''
    subprocess.check_call(
        "git tag --delete " + tag_name(version),
        stderr=subprocess.PIPE,
        shell=True)


def tag_create(version):
    ''' Creates the tag for the specified version
        Returns True if tag created
    '''
    print "Creating release tag '%s'" % tag_name(version)
    result = subprocess.call(
        "git tag --sign --message '%s' %s" % (
            "ADOdb version %s released %s" % (
                version,
                release_date(version)
            ),
            tag_name(version)
        ),
        shell=True
    )
    return result == 0


def update_changelog(version):
    ''' Updates the release date in the Change Log
    '''
    print "Updating Changelog"

    # Development release
    # Insert a new section for next release before the most recent one
    if version.endswith(_version_dev):
        version_release = version[:-len(_version_dev)]

        version_previous = version_release.split(".")
        version_previous[1] = str(int(version_previous[1]) - 1)
        version_previous = ".".join(version_previous)

        print "  Inserting new section for v%s" % version_release
        script = "1,/^##/s/^##.*$/## %s - %s\\n\\n\\0/" % (
            version_release,
            release_date(version)
            )

    # Stable release (X.Y.0 or X.Y)
    # Replace the occurence of markdown level 2 header matching version
    # and release date patterns
    elif version.endswith(".0") or re.match('[Vv]?[0-9]\.[0-9]+$', version):
        print "  Updating release date for v%s" % version
        script = "1,/^##/s/^(## )%s - %s.*$/\\1%s - %s/" % (
            _version_regex,
            _release_date_regex,
            version,
            release_date(version)
            )

    # Hotfix release (X.Y.[0-9] or X.Y[a-z])
    # Insert a new section for the hotfix release before the most recent
    # section for version X.Y and display a warning message
    else:
        version_parent = re.match('[Vv]?([0-9]\.[0-9]+)', version).group(1)
        print "  Inserting new section for hotfix release v%s" % version
        print "  WARNING: review '%s' to ensure added section is correct" % (
            _changelog_file
            )
        script = "1,/^## {0}/s/^## {0}.*$/## {1} - {2}\\n\\n\\0/".format(
            version_parent,
            version,
            release_date(version)
            )

    subprocess.call(
        "sed -r -i '%s' %s " % (
            script,
            _changelog_file
        ),
        shell=True
    )
#end update_changelog


def version_set(version, do_commit=True, do_tag=True):
    ''' Bump version number and set release date in source files
    '''
    print "Preparing version bump commit"

    update_changelog(version)

    print "Updating version and date in source files"
    subprocess.call(
        "sed -r -i '%s' %s " % (
            sed_script(version),
            " ".join(sed_filelist())
        ),
        shell=True
    )
    print "Version set to %s" % version

    if do_commit:
        # Commit changes
        print "Committing"
        commit_ok = subprocess.call(
            "git commit --all --message '%s'" % (
                "Bump version to %s" % version
            ),
            shell=True
        )

        if do_tag:
            tag_ok = tag_create(version)
        else:
            tag_ok = False

        if commit_ok == 0:
            print '''
NOTE: you should carefully review the new commit, making sure updates
to the files are correct and no additional changes are required.
If everything is fine, then the commit can be pushed upstream;
otherwise:
 - Make the required corrections
 - Amend the commit ('git commit --all --amend' ) or create a new one'''

            if tag_ok:
                print ''' - Drop the tag ('git tag --delete %s')
 - run this script again
''' % (
                    tag_name(version)
                )

    else:
        print "Note: changes have been staged but not committed."
#end version_set()


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
        print "ERROR: please specify the version"
        sys.exit(1)

    do_commit = False
    do_tag = False

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-c", "--commit"):
            do_commit = True

        elif opt in ("-t", "--tag"):
            do_tag = True

    # Mandatory parameters
    version = version_check(args[0])

    # Let's do it
    version_set(version, do_commit, do_tag)
#end main()


if __name__ == "__main__":
    main()
