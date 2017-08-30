_[< previous chapter](04-VPN-Access.md) | [next chapter >](06-Stack-Operations.md)_

# 🎛 Administration

_This chapter is dedicated for Administrators for reference and answering common questions. At this point in the setup, your cloud is ready for use. However, it is best to thoroughly review the content in the chapter before "going live"._

## GENERAL INFORMATION

### Are there any known limitations with this solution?

- The OpenEMR Patient Portal is not included.
- OpenEMR multi-site mode is not supported.
- There is no warranty. You (or the IT department) should become familiar with cloud administration and have a user-friendly support process in place.

### How much will OpenEMR cost to operate per month?

Amazon's cost calculator* suggests $135/month for the immediately obvious resources as configured; discounts of forty to sixty percent are possible via the purchase of long-term reservations. Assuming 25% inflation for ongoing fees like storage and IO, it is $170/month before the cost of any Amazon support contract. This is only an estimate and users are encouraged to submit their actual bills amounts to get a better idea of the costs under a production load.

_* In us-east-1. Other regions will tend to be more expensive._

### How can I monitor my costs?

You can always browse your monthly bill-to-date in your billing dashboard &mdash; click on your name in the upper-right corner of any AWS console page, next to the region. (You may need to [activate billing access](https://aws.amazon.com/premiumsupport/knowledge-center/iam-billing-access/) for your IAM user before you're able to see this information without your root credentials.)

Alternatively, you may configure a billing alert, which will let you know if your projected expenses are higher than you expect them to be.

1. Click on **Services**, then **CloudWatch**, then **Alarms**, then **Billing**, then **Create Alarm**.
2. Click on **Billing Metrics**, then **Total Estimated Charge**. Click the check-box beside "Currency", then click **Next**.
3. Name and describe the alarm, then set the threshold **&gt;=** to "200", or your preference.
4. Under **Actions**, click the small link called "New List". In the box that appeared below, enter your email address, and in the box above that (currently filled with "Enter a topic name here...") enter "BillingAlerts". Click **Create Alarm**.
5. Don't forget to click on the subscription in your email! Take a moment to do so, and then click **View Alarm**.

_Note that if you're using partial-upfront reserved instances, the front-loaded first-day-of-the-month billing will skew the 'projected expenses' because the projections don't take into account the singular nature of the expense._

## DEPLOYMENTS AND MAINTENANCE

### What are the recommendations for tracking custom code changes?

You'll want to have an _exact copy of what is running on the cloud_. The initial setup uses this slightly customized OpenEMR codebase that you'll want to [download here](https://sourceforge.net/projects/openemr/files/OpenEMR%20Cloud/5.0.0/openemr-v5-0-0-4-cloud.tar.gz/download). Note that you must base your changes off of this codebase and not any other source.

It is very important to hold this local copy of OpenEMR. If you are planning on making a lot of code customizations, it is best to use [Git with a centralized cloud setup](https://www.sitepoint.com/git-for-beginners/). This approach makes certain that no changes are lost and multiple team members can access the code.

Regardless of if you planning on making a lot or a few changes to the OpenEMR source code, it is recommended to keep a running document of how to re-apply said changes when upgrading your OpenEMR codebase. This is best done in the Git repository via a markdown file should you choose that route. Otherwise, consider something like a [cloud file storage solution](http://www.makeuseof.com/tag/dropbox-vs-google-drive-vs-onedrive-cloud-storage-best/) to centrally and safely store the document.

### How do I deploy custom changes to my cloud?

The most robust and maintainable approach for deployments is to keep an internal changelog of your changes along with associated [version control tags](https://git-scm.com/book/en/v2/Git-Basics-Tagging). Not only will this help you stay organized, but you can also reference it in the case you wish to rollback to a previous deployment and aid in reapplying your custom changes when a newer version of OpenEMR is available.

1. In the AWS Management Console, click **Services**, **Elastic Beanstalk**, and then choose your environment.
2. Click the **Upload and Deploy** button in the center of the screen.
3. Click **Choose File** and select "**openemr.zip**". Note that the name of this file must be exact.
4. Under **Label**, enter in **"openemr-deployment-N"** where **N** is most recent version of your deployment.
5. Click **Deploy**.

### What are the recommendations for development and testing?

If you aren't planning on customizing OpenEMR source code, you can simply use one AWS environment. Otherwise, it is best to break out the environments as follows:

- **local** - A local installation of OpenEMR for developers to code against. Refer to the [wiki](http://www.open-emr.org/wiki/index.php/OpenEMR_Downloads) to see how to set it up on Debian-based Linux and Windows.

- **dev** - A small resources AWS environment for developers to try out their local code changes on. Although developers will have a local OpenEMR installation to work with, it is best to have an environment for testing these changes on an actual cloud environment. You'll want to adjust the CloudFormation stack to use very small instances and select "Dev/Test" in the RDS section to save money.

- **test** - A small resources AWS environment for testers to ensure new code changes work. This is different from dev in that it testers may use a special dataset to test code changes more realistically and, unlike dev, it is dedicated to testers so that the developers can make changes to their environment without impacting the testing efforts. You'll want to adjust the CloudFormation stack to use very small instances and select "Dev/Test" in the RDS section to save money.

- **stage** - This is an AWS environment identical to production for final testing efforts. Unlike dev and test, stage may contain a mirror of actual production data to achieve the most realistic verification before applying code changes to production.

- **production** - This is the live AWS environment that the users are using. Code changes should only be applied to production after going through dev, test, and stage.

### What does the architecture look like?

1. In the AWS Management Console, click **Services** and then click **CloudFormation**.
2. Review the table and checkbox the row with **Stack Name** of **OpenEMR**.
3. Near the top, click **Actions** and then click **View/Edit template in Designer**.
4. Observe the diagram to understand the system architecture.

### Can I use other regions?

As of this writing, only five AWS regions support the EFS service that the Elastic Beanstalk services are using to coordinate server state with each other. Of those five,
Ohio (us-east-2) has reliability problems with Elastic Beanstalk deployments from CloudFormation that have not yet been resolved. You could replace EFS with a vanilla NFS server, and then you'd be able to deploy in a foreign region if you updated the mappings accordingly, but doing so will introduce a single-zone point of failure that EFS doesn't share.

### How do I access system logs?

1. In the AWS Management Console, click **Services**, **Elastic Beanstalk**, and then choose your environment.
2. In the left pane, click **Logs**.
3. Click the **Request Logs** button to the to right of the screen.
4. Click **Full Logs** and wait a moment for the logs to download.
5. Extract the contents with your favorite archive extractor to view each instance's Apache logs in **logs_directory/var/log/httpd**.

### How do I access audit logs?

1. In the AWS Management Console, click **Services** and then click **S3**.
2. Find the new bucket that CloudFormation created. The bucket will have a name with this format: **openemr-<hexadecimal uuid>**.
3. Click into the bucket, then **AWSLogs**, then **\<_your account ID_\>**, then **CloudTrail**.
4. Here, CloudTrail will store your AWS activity with a hierarchy of **region/year/month/day**. The data saved in these logs will be useful to administrators and auditors. Note this location in a safe place.

### Can I backup the RDS MySQL database?

RDS does this for you and this solution has great defaults for its configuration. If you want to understand how to restore a backup, please review http://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/USER_WorkingWithAutomatedBackups.html and http://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/CHAP_CommonTasks.BackupRestore.html.

### Can I backup the EFS NFS drive?

The **EFS Backup Server** instance makes automated daily backups of that filesystem via Duplicity and the S3 bucket we allocate for the solution. If you ssh into that Ubuntu server, you can see backup and restore scripts available in /root.

### Can I backup the CouchDB patient documents?

An AWS Lambda function creates and manages daily backups of EBS volume housing the CouchDB document store.

### How do I access CloudTrail audit logs?

1. Click on **Services** and then click **S3**.
2. Look for the bucket with a name following this format: **\<_your account ID_\>-cloudtrail-logs**.
3. Click into the bucket, then **AWSLogs**, then **\<_your account ID_\>**, then **CloudTrail**.

### What's left if I delete the CloudFormation stack?

If you're just testing the stack out, please note that deleting the stack will not erase all traces of the deployment from Amazon. You'll need to find and delete:

 * The master encryption key (in IAM).
 * The CouchDB volume snapshots (in EC2).
 * The final database snapshot (in RDS).
 * The NFS instance (in Elastic File System).
 * The administrative bucket (in S3).

See the **Resources** tab of the stack for the IDs of these objects.

### Can I change the size of the MySQL Database?

Yes. Review http://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/USER_ModifyInstance.MySQL.html for more information.

### Can I change the size of the CouchDB Database?

The CloudFormation has a sensible default with an option to customize the size yourself. It is advised to pick the right size for your use case at the start. However, if you need to change it afterward, review https://matt.berther.io/2015/02/03/how-to-resize-aws-ec2-ebs-volumes/. Note that the backup solution will no longer work though because the volume ID will be different. This will have to be manually remedied in the Lambda function Python code.

### Can I use a domain name purchased outside of the Amazon registrar?

If your domain isn't registered through Amazon, you can still host it with Route 53 by changing your domain's "NS" entries at your registrar to the NS records showing on your domain here at Amazon. That will tell the world that Amazon is the one responsible for the content of the zone file, and will let the changes you're about to make be publically visible. Alternately, you can leave your domain hosted off-Amazon entirely, but the automated SES-to-Route-53 setup in a few sections won't be available, and you'll have to modify records manually.

### Can I increase the load balancer capacity in ElasticBeanstalk?

1. In the AWS Management Console, click **Services**, **Elastic Beanstalk**, and then choose your environment.
2. In the left pane, click **Configuration**.
3. Click **Scaling** and adjust the quantity accordingly.

## SYSTEMS ACCESS

### How do I access the database?

1. Connect to OpenVPN. For more information, see the "**Prerequisites**" section below.
2. Assuming you have MySQL or simply a MySQL client library installed, perform your MySQL work by running `mysql -u openemr_db_user -p -h (noted RDS endpoint without port) openemr`
3. When prompted, enter your password.
4. Type `use openemr;` and hit enter.

### How do I SSH into instances?

Accessing your instances with SSH is one of the more challenging tasks in this guide. As such, be sure to treat this as a learning opportunity and pay close attention to the instructions to ensure the most seamless experience.

#### Prerequisites

1. This section assumes that you've already setup OpenVPN. If this is not the case, see chapter 4.
2. Download and install the latest [PuTTY MSI](https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html) software suite. If you aren't sure, click [here](https://the.earth.li/~sgtatham/putty/latest/w64/putty-64bit-0.69-installer.msi).
3. Using your AWS SSH keypair that is saved as "**your-username.pem**", convert it to a **ppk** file by following [these instructions](http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/putty.html#putty-private-key).

_Note: If you are not already connected to OpenVPN, be sure that the **OpenVPN Connect** program is running. The **Server** field should be the public OpenVPN IP that was noted in chapter 4 and the **Username** field is "**openvpn**"._

#### OpenVPN

Using your "**your-username.ppk**" keypair, access your instance by following [these instructions](http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/putty.html#putty-ssh). Note that step 1 can be skipped. Also note that "**user_name@public_dns_name**" is "**openvpnas@(your noted public OpenVPN ip)**".

#### CouchDB access

Using your "**your-username.ppk**" keypair, access your instance by following [these instructions](http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/putty.html#putty-ssh). Note that step 1 can be skipped. Also note that "**user_name@public_dns_name**" is "**ubuntu@couchdb.openemr.local**".

#### Elastic Beanstalk instance access

1. In the AWS Management Console, click **Services**, **EC2**, and then **Running Instances**.
2. Select an instance with a name similar to the format **Open-EBEn-EDQ2JSVVZGYD**. All instances are identical in configuration.
3. Under **Private IP**, note the address.
4. Using your "**your-username.ppk**" keypair, access your instance by following [these instructions](http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/putty.html#putty-ssh). Note that step 1 can be skipped. Also note that "**user_name@public_dns_name**" is "**ec2-user@(your noted internal ec2 ip)**".
