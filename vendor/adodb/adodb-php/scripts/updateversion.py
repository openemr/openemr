#!/usr/bin/python -u
'''
    ADOdb version update script

    Updates the version number, and release date in all php and html files
'''

from datetime import date
import getopt
import os
from os import path
import re
import subprocess
import sys


# ADOdb version validation regex
# These are used by sed - they are not PCRE !
_version_dev = "dev"
_version_regex = "[Vv]?([0-9]\.[0-9]+)(\.([0-9]+))?(-?%s)?" % _version_dev
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


def version_is_dev(version):
    ''' Returns true if version is a development release
    '''
    return version.endswith(_version_dev)


def version_is_patch(version):
    ''' Returns true if version is a patch release (i.e. X.Y.Z with Z > 0)
    '''
    return not version.endswith('.0')


def version_parse(version):
    ''' Breakdown the version into groups (Z and -dev are optional)
        1:(X.Y), 2:(.Z), 3:(Z), 4:(-dev)
    '''
    return re.match(r'^%s$' % _version_regex, version)


def version_check(version):
    ''' Checks that the given version is valid, exits with error if not.
        Returns the SemVer-normalized version without the "v" prefix
        - add '.0' if missing patch bit
        - add '-' before dev release suffix if needed
    '''
    vparse = version_parse(version)
    if not vparse:
        usage()
        print "ERROR: invalid version ! \n"
        sys.exit(1)

    vnorm = vparse.group(1)

    # Add .patch version component
    if vparse.group(2):
        vnorm += vparse.group(2)
    else:
        # None was specified, assume a .0 release
        vnorm += '.0'

    # Normalize version number
    if version_is_dev(version):
        vnorm += '-' + _version_dev

    return vnorm


def get_release_date(version):
    ''' Returns the release date in DD-MMM-YYYY format
        For development releases, DD-MMM will be ??-???
    '''
    # Development release
    if version_is_dev(version):
        date_format = "??-???-%Y"
    else:
        date_format = "%d-%b-%Y"

    # Define release date
    return date.today().strftime(date_format)


def sed_script(version):
    ''' Builds sed script to update version information in source files
    '''

    # Version number and release date
    script = r"s/{}\s+(-?)\s+{}/v{} \5 {}/".format(
        _version_regex,
        _release_date_regex,
        version,
        get_release_date(version)
    )

    return script


def sed_filelist():
    ''' Build list of files to update
    '''
    dirlist = []
    for root, dirs, files in os.walk(".", topdown=True):
        # Filter files by extensions
        files = [
            f for f in files
            if re.search(r'\.(php|html?)$', f, re.IGNORECASE)
            ]
        for fname in files:
            dirlist.append(path.join(root, fname))

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
                get_release_date(version)
            ),
            tag_name(version)
        ),
        shell=True
    )
    return result == 0


def section_exists(filename, version, print_message=True):
    ''' Checks given file for existing section with specified version
    '''
    script = True
    for i, line in enumerate(open(filename)):
        if re.search(r'^## ' + version, line):
            if print_message:
                print "  Existing section for v%s found," % version,
            return True
    return False


def version_get_previous(version):
    ''' Returns the previous version number
        Don't decrease major versions (raises exception)
    '''
    vprev = version.split('.')
    item = len(vprev) - 1

    while item > 0:
        val = int(vprev[item])
        if val > 0:
            vprev[item] = str(val - 1)
            break
        else:
            item -= 1

    if item == 0:
        raise ValueError('Refusing to decrease major version number')

    return '.'.join(vprev)


def update_changelog(version):
    ''' Updates the release date in the Change Log
    '''
    print "Updating Changelog"

    vparse = version_parse(version)

    # Version number without '-dev' suffix
    version_release = vparse.group(1) + vparse.group(2)
    version_previous = version_get_previous(version_release)

    if not section_exists(_changelog_file, version_previous, False):
        raise ValueError(
            "ERROR: previous version %s does not exist in changelog" %
            version_previous
            )

    # Check if version already exists in changelog
    version_exists = section_exists(_changelog_file, version_release)
    if (not version_exists
            and not version_is_patch(version)
            and not version_is_dev(version)):
        version += '-' + _version_dev

    release_date = get_release_date(version)

    # Development release
    # Insert a new section for next release before the most recent one
    if version_is_dev(version):
        # Check changelog file for existing section
        if version_exists:
            print "nothing to do"
            return

        # No existing section found, insert new one
        if version_is_patch(version_release):
            print "  Inserting new section for hotfix release v%s" % version
        else:
            print "  Inserting new section for v%s" % version_release
            # Adjust previous version number (remove patch component)
            version_previous = version_parse(version_previous).group(1)
        script = "1,/^## {0}/s/^## {0}.*$/## {1} - {2}\\n\\n\\0/".format(
            version_previous,
            version_release,
            release_date
            )

    # Stable release (X.Y.0)
    # Replace the 1st occurence of markdown level 2 header matching version
    # and release date patterns
    elif not version_is_patch(version):
        print "  Updating release date for v%s" % version
        script = r"s/^(## ){0}(\.0)? - {1}.*$/\1{2} - {3}/".format(
            vparse.group(1),
            _release_date_regex,
            version,
            release_date
            )

    # Hotfix release (X.Y.[0-9])
    # Insert a new section for the hotfix release before the most recent
    # section for version X.Y and display a warning message
    else:
        if version_exists:
            print 'updating release date'
            script = "s/^## {0}.*$/## {1} - {2}/".format(
                version.replace('.', '\.'),
                version,
                release_date
                )
        else:
            print "  Inserting new section for hotfix release v%s" % version
            script = "1,/^## {0}/s/^## {0}.*$/## {1} - {2}\\n\\n\\0/".format(
                version_previous,
                version,
                release_date
                )

        print "  WARNING: review '%s' to ensure added section is correct" % (
            _changelog_file
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
    os.chdir(subprocess.check_output('git root', shell=True).rstrip())
    version_set(version, do_commit, do_tag)
#end main()


if __name__ == "__main__":
    main()
