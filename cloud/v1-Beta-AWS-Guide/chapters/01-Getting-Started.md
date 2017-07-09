_[next chapter >](02-Private-Cloud.md)_

# 🚴 Getting Started

### Start by getting a local copy of OpenEMR v5

1. Download [this file](https://sourceforge.net/projects/openemr/files/OpenEMR%20Cloud/5.0.0/openemr-v5-0-0-2-cloud.tar.gz/download) to your computer. Extract it and make sure the folder is named `openemr` (note that you may have to extract the tar twice if you are on Windows).

### Create an AWS Account

1. Navigate to [https://aws.amazon.com/](https://aws.amazon.com/), and then click **Create an AWS Account**.
2. Follow along with the signup wizard.

### Add yourself as an administrative user

1. Now that you are logged into the AWS Management Console, click **Services** and then click **IAM**.
2. In the left pane, click **Dashboard**, and copy down the IAM user sign-in link.
3. In the left pane, click **Users**.
4. Click **Add user**.
5. Under **Set user details**, enter your username in the **User name** field.
6. Under **Select AWS access type**, select only **AWS Management Console access** in the **Access type** area.
7. Enter a [strong password](https://www.random.org/passwords/?num=1&len=16&format=html&rnd=new) for the **Console password** field. Note this in a safe place.
8. Click **Next: Permissions**.
9. Under **Set permissions for ...**, click the **Attach existing policies directly**  box.
10. With the table at the bottom of the page in view, select **AdministratorAccess** (will be the first row).
11. Click **Next: Review**.
12. Under **Review**, ensure all information reflects the above steps.
13. Click **Next: Create user**.
14. Log out of the AWS console, go to the sign-in link you copied down in step 2, and log in with your new credentials.
15. (_Optional but highly recommended step_): Enable two-factor authentication via the **Security credentials** tab of your user profile in IAM. Click the pencil beside **Assigned MFA Device** to start this process.

### Select an AWS Region

This guide uses services that are _only_ available in certain AWS regions. As of this writing, you will need to make sure you're in one the of five Amazon regions described below.

1. In the AWS Management Console, click **Services**, and then click **EC2**.
2. In the region dropdown in the top right corner, select either "**Ohio**", "**N. Virginia**", "**Oregon**", "**Ireland**", or "**Sydney**". Be sure to remain in this region for the remainder of this guide.

### Generate an AWS SSH keypair

1. In the AWS Management Console, click **Services** and then click **EC2**.
2. In the left hand pane, under **Network & Security**, click **Key Pairs**.
3. Click **Create Key Pair**.
4. When the **"Create Key Pair"** dialog appears, enter your username for the **Key pair name** field and click **Create**.
5. When the **Save As** dialog appears, save the .pem keyfile to a safe place.
