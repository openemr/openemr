#!/usr/bin/python -u
'''
    ADOdb release build script

    - Create release tag if it does not exist
    - Copy release files to target directory
    - Generate zip/tar balls
    -
'''

import errno
import getopt
import re
import os
from os import path
import shutil
import subprocess
import sys
import tempfile

import updateversion


# ADOdb Repository reference
origin_repo = "https://github.com/ADOdb/ADOdb.git"
release_branch = "master"
release_prefix = "adodb"

# Directories and files to exclude from release tarballs
exclude_list = (".git*",
                "replicate",
                "scripts",
                "tests",
                # There are no png files in there...
                # "cute_icons_for_site/*.png",
                "hs~*.*",
                "adodb-text.inc.php",
                # This file does not exist in current repo
                # 'adodb-lite.inc.php'
                )

# Command-line options
options = "hb:dfk"
long_options = ["help", "branch", "debug", "fresh", "keep"]

# Global flags
debug_mode = False
fresh_clone = False
cleanup = True


def usage():
    print '''Usage: %s [options] version release_path

    Parameters:
        version                 ADOdb version to bundle (e.g. v5.19)
        release_path            Where to save the release tarballs

    Options:
        -h | --help             Show this usage message

        -b | --branch <branch>  Use specified branch (defaults to '%s' for '.0'
                                releases, or 'hotfix/<version>' for patches)
        -d | --debug            Debug mode (ignores upstream: no fetch, allows
                                build even if local branch is not in sync)
        -f | --fresh            Create a fresh clone of the repository
        -k | --keep             Keep build directories after completion
                                (useful for debugging)
''' % (
        path.basename(__file__),
        release_branch
    )
#end usage()


def set_version_and_tag(version):
    '''
    '''
    global release_branch, debug_mode, fresh_clone, cleanup

    # Delete existing tag to force creation in debug mode
    if debug_mode:
        try:
            updateversion.tag_delete(version)
        except:
            pass

    # Checkout release branch
    subprocess.call("git checkout %s" % release_branch, shell=True)

    if not debug_mode:
        # Make sure we're up-to-date, ignore untracked files
        ret = subprocess.check_output(
            "git status --branch --porcelain --untracked-files=no",
            shell=True
        )
        if not re.search(release_branch + "$", ret):
            print "\nERROR: branch must be aligned with upstream"
            sys.exit(4)

    # Update the code, create commit and tag
    updateversion.version_set(version)

    # Make sure we don't delete the modified repo
    if fresh_clone:
        cleanup = False


def main():
    global release_branch, debug_mode, fresh_clone, cleanup

   # Get command-line options
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError, err:
        print str(err)
        usage()
        sys.exit(2)

    if len(args) < 2:
        usage()
        print "ERROR: please specify the version and release_path"
        sys.exit(1)

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-b", "--branch"):
            release_branch = val

        elif opt in ("-d", "--debug"):
            debug_mode = True

        elif opt in ("-f", "--fresh"):
            fresh_clone = True

        elif opt in ("-k", "--keep"):
            cleanup = False

    # Mandatory parameters
    version = updateversion.version_check(args[0])
    release_path = args[1]

    # Default release branch
    if updateversion.version_is_patch(version):
        release_branch = 'hotfix/' + version

    # -------------------------------------------------------------------------
    # Start the build
    #
    global release_prefix

    print "Building ADOdb release %s into '%s'\n" % (
        version,
        release_path
    )

    if debug_mode:
        print "DEBUG MODE: ignoring upstream repository status"

    if fresh_clone:
        # Create a new repo clone
        print "Cloning a new repository"
        repo_path = tempfile.mkdtemp(prefix=release_prefix + "-",
                                     suffix=".git")
        subprocess.call(
            "git clone %s %s" % (origin_repo, repo_path),
            shell=True
        )
        os.chdir(repo_path)
    else:
        repo_path = subprocess.check_output('git root', shell=True).rstrip()
        os.chdir(repo_path)

        # Check for any uncommitted changes
        try:
            subprocess.check_output(
                "git diff --exit-code && "
                "git diff --cached --exit-code",
                shell=True
                )
        except:
            print "ERROR: there are uncommitted changes in the repository"
            sys.exit(3)

        # Update the repository
        if not debug_mode:
            print "Updating repository in '%s'" % os.getcwd()
            try:
                subprocess.check_output("git fetch", shell=True)
            except:
                print "ERROR: unable to fetch\n"
                sys.exit(3)

    # Check existence of Tag for version in repo, create if not found
    try:
        updateversion.tag_check(version)
        if debug_mode:
            set_version_and_tag(version)
    except:
        set_version_and_tag(version)

    # Copy files to release dir
    release_files = release_prefix + version.split(".")[0]
    release_tmp_dir = path.join(release_path, release_files)
    print "Copying release files to '%s'" % release_tmp_dir
    retry = True
    while True:
        try:
            shutil.copytree(
                repo_path,
                release_tmp_dir,
                ignore=shutil.ignore_patterns(*exclude_list)
            )
            break
        except OSError, err:
            # First try and file exists, try to delete dir
            if retry and err.errno == errno.EEXIST:
                print "WARNING: Directory '%s' exists, delete it and retry" % (
                    release_tmp_dir
                )
                shutil.rmtree(release_tmp_dir)
                retry = False
                continue
            else:
                # We already tried to delete or some other error occured
                raise

    # Create tarballs
    print "Creating release tarballs..."
    release_name = release_prefix + '-' + version
    print release_prefix, version, release_name

    os.chdir(release_path)
    print "- tar"
    subprocess.call(
        "tar -czf %s.tar.gz %s" % (release_name, release_files),
        shell=True
    )
    print "- zip"
    subprocess.call(
        "zip -rq %s.zip %s" % (release_name, release_files),
        shell=True
    )

    if cleanup:
        print "Deleting working directories"
        shutil.rmtree(release_tmp_dir)
        if fresh_clone:
            shutil.rmtree(repo_path)
    else:
        print "\nThe following working directories were kept:"
        if fresh_clone:
            print "- '%s' (repo clone)" % repo_path
        print "- '%s' (release temp dir)" % release_tmp_dir
        print "Delete them manually when they are no longer needed."

    # Done
    print "\nADOdb release %s build complete, files saved in '%s'." % (
        version,
        release_path
    )
    print "Don't forget to generate a README file with the changelog"

#end main()

if __name__ == "__main__":
    main()
