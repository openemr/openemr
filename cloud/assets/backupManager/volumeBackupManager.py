#!/usr/bin/python

# volumeBackupManager.py: automated hot-snapshot faculty for EC2 volume

# This is expected to be run from an AWS Lambda instance with an appropriate IAM role and
# arguments passed in from the environment, but concessions have been made to allow console
# testing via the command-line; it could also run fine from a cron.

# Note that this script does NOT unmount or otherwise pause the volume it's backing up, and
# is therefore unsuitable for general purpose use where arrangements for consistency have not
# been made.

# Note: The stack is currently using a pared-down inline version of this script, but I leave this
# here to aid testing and debugging. Zip this file and the template up to create a Lambda deployment
# package. 

import argparse
import urllib2
import boto3
import os

if not os.environ.has_key('AWS_DEFAULT_REGION'):
    # we're not operating in AWS Lambda
    lambda_handler(None, None);

def lambda_handler(event, context):
    defaultRetainCount = 3
    defaultDescription = 'automated backup from volumeBackupManager'

    parser = argparse.ArgumentParser(description="automated hot-snapshot faculty for EC2 volume")
    parser.add_argument("--region", help="ec2 region for volume")
    parser.add_argument("--volume", help="ec2 volume id for backup")
    parser.add_argument("--retain", help="max versions to retain [" + str(defaultRetainCount) + "]")
    parser.add_argument("--description", help="brief note")
    parser.add_argument("--skip", help="reap backups but don't make one", action="store_true")
    args = parser.parse_args()

    if args.region:
        localRegion = args.region
    elif os.environ.has_key('AWS_DEFAULT_REGION'):
        localRegion = os.environ['AWS_DEFAULT_REGION']
    else:
        localRegion = urllib2.urlopen('http://169.254.169.254/latest/meta-data/placement/availability-zone').read()[:-1]

    if args.volume:
        backupVolumeId = args.volume
    elif os.environ.has_key('VOLUME_ID'):
        backupVolumeId = os.environ['VOLUME_ID']
    else:
        raise Exception('no given volume to backup')

    if args.retain:
        retainCount = args.retain
    elif 'COUNTRETAINED' in os.environ:
        retainCount = os.environ['COUNTRETAINED']
    else:
        retainCount = defaultRetainCount

    if args.description:
        backupDescription = args.description
    elif 'DESCRIPTION' in os.environ:
        backupDescription = os.environ['DESCRIPTION']
    else:
        backupDescription = defaultDescription

    awsSession = boto3.session.Session(region_name = localRegion)
    ec2 = awsSession.resource('ec2')

    volume = ec2.Volume(backupVolumeId)

    if not args.skip:
        volume.create_snapshot(Description = backupDescription)

    snapshots = sorted(volume.snapshots.all(), key=lambda x: x.start_time)

    if len(snapshots) > retainCount:
        for i in range(0,len(snapshots)-retainCount):
            print 'delete: ' , snapshots[i]
            snapshots[i].delete()

    return 'all OK'
