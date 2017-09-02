_[< previous chapter](05-Administration.md)_

# Stack Operations

Up until now, we've only discussed the basic form of the OpenEMR stack, a single-region OpenEMR installation lacking easy developer hooks or full-stack recovery options. This chapter is targeted at experienced administrators and, unfortunately, can make few concessions to users unfamiliar with Python package management or general DevOps chores. Although the contents of this chapter are important for _someone_ in your organization (or your contractor) to at least be familiar with, it doesn't have to be you and it doesn't have to be right now.

## stack.py

The CloudFormation template is constructed from ``/cloud/assets/troposphere/stack.py`` via the Troposphere library, taking command-line options and emitting the constructed CFN stack to standard output. It can take the following options:

 * **--dual-az**: Builds a stack capable of running in two AWS Availability Zones, and continuing to function even if one AZ is down.
 * **--beanstalk-key BEANSTALK-KEY**: Use a non-standard Elastic Beanstalk application archive. (Expect to hardcode the mappings to use your own regional deployment buckets.)
 * **--recovery**: Builds a recovery OpenEMR stack that can accept snapshots and backups and restore the entire, configured application.
 * **--dev**: Constructs a stack in developer mode, which will make the following concessions.
   * Delete, instead of snapshot, as many resources as possible when the stack is deleted.
   * Create a world-visible bastion instance you can ssh (and key forward) into, to enable easy instant access to stack internals without requiring OpenVPN. Be warned: This will **breach HIPAA** if used with live patient data, and should be reserved for testing purposes only. Preventing unthinking misuse of this feature is why a developer version of the stack is not provided in the codebase by default. See "DeveloperKeyhole" in the stack outputs for the public IP of this instance.
 * **--force-bastion**: Constructs the developer bastion (as above) without changing the rest of the stack's construction.

 ```
 $ cd cloud/assets/troposphere
 $ pip install -r requirements.txt
 $ python stack.py --recovery > stack.json
 $ $EDITOR stack.json
 ```

 The CloudFormation template is a difficult read, but ``stack.py`` is significantly better-organized, which is good because you may find it necessary to modify it for your own specific tastes or environment. Make the change, re-run ``stack.py``, and you can now manually create a new stack in the CloudFormation manager, uploading your just-produced stack on request. Note that the AWS stack builder tool can (within limits) verify the correctness of a stack before you attempt to launch it, and don't forget that AWS constantly evolves. You may need to update your Troposphere library to make use of recently-released AWS features with new CloudFormation elements and references.

## Using the recovery stack

Making backups is important, but an untested backup is no backup at all. Scheduling and implementing regular tests of your backups to ensure they can successfully recover your data is the single most-neglected task in IT, and to this end we provide a recovery stack, capable of taking the automated cloud backups and building the entire OpenEMR application stack from first principles. Use this facility not just to restore your application in the event of catastrophe, but to regularly insure that you **can** successfully recover your application, both in terms of backup validity and the expertise required to use the tools.

### Before you begin

 * If you're using custom OpenEMR code, be sure that your revised, deployed beanstalk file is in S3, and modify the stack file to hardcode your new archive bucket.
 * The backup will require a full copy of the EFS mount, which must be performed /after/ the initial configuration of the system. This process normally happens once a day, but if you're seeking to perform a backup test immediately after installation, you may find it necessary to connect to the NFS Backup instance and run /root/backup.sh **after** you've completed OpenEMR setup. You can confirm that the backup has been successfully run by seeing a selection of .gz files in your S3 bucket's /Backup location.
 * You're welcome to manually run the backups yourself, to ensure all three of the snapshots are taken at the same time. In production, there's no guarantee the backups will happen at the same time of day, which might cause odd desynchronizations between the patient records and patient documents. You might consider modifying the stack (or just the elements after the fact) to ensure that the RDS snapshot, the Lambda-powered document snapshot, and the cron-powered daily EFS backup all happen around the same time each day.
 * Only one of the two CouchDB masters is backed up -- since they're in master-master replication, this should be fine, but you may consider creating and keeping the other master's snapshot around too.
 * If you want to restore in a new region, copy the snapshots to the new region before you begin.

### Recover your backups

  1. Run ``stack.py <your options> --recovery > OpenEMR-Recovery.json``.
  2. Start this stack in CloudFormation. You'll have four questions you aren't familiar with.
     * **RecoveryCouchDBSnapshot**: The EC2 volume snapshot of the EC2 volume from the Patient Documents. (example: ``snap-0ebb4f155ff27040c``)
     * **RecoveryKMSKey**: The ARN of the KMS key created by the original stack, which protects all the resources you're restoring. (example: ``arn:aws:kms:us-east-1:7...3:key/6fc10c90-d550-4fd5-bb5c-c2416e31839a``)
     * **RecoveryRDSSnapshotARN**: The ARN of the RDS database from the original stack. (example: ``arn:aws:rds:us-east-1:7...3:snapshot:openemr063-backup``)
     * **RecoveryS3Bucket**: The name of the bucket created by the original stack. (example: ``openemr-c49525c0-82e5-11e7-bcf4-50faeaa96461``)
     * (Questions like database password, volume size, and timezone are going to be deduced from the recovered resources.)
  3. Reassign front-end SSL, per chapter 3. Reassigning DNS is optional if you're only testing your backup.

### Reconfigure your stack

  1. You can now log in with your administration credentials, and verify the data and records have been successfully reloaded and that prior configuration settings have been retained.
  2. Reconfigure your VPN per chapter 4, if this is a full, long-term recovery stack.
  3. Avoid doing anything that would send email or alerts to patients, if you're testing with live patient data. If any OpenEMR plugins can be switched into a test mode, consider doing so.
