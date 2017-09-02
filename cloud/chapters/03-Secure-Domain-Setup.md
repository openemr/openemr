_[< previous chapter](02-Application-Servers.md) | [next chapter >](04-VPN-Access.md)_

# ▶ Secure Domain Setup

### Purchase Custom Domain

1. In the AWS Management Console, click **Services** and then **Route 53**.
2. Under **Register domain**, enter your desired domain name and then click **Check**.
3. Assuming the domain is available, click **Add to cart** next to your desired domain name. Note the domain name in a safe place.
4. Scroll down to the bottom of the page and click **Continue**.
5. Enter your **Registrant**, **Administrative**, and **Technical** contact information and then click **Continue** at the bottom of the page.
6. Under **Terms and Conditions**, checkbox the agreement then click **Complete Purchase**.
7. Wait around 10 minutes for an AWS email to be sent to you regarding this domain. Follow the instructions that are sent in said email.

### Create a Hosted Zone

1. In the AWS Management Console, click **Services** and then **Route 53**.
2. Click **Go To Domains** in the center of the screen.
3. In the left hand pane, click **Hosted zones**.
4. Click **Create Hosted Zone** to the top left.
5. Checkbox your recently created domain name and then click **Create Hosted Zone** to the top left.
6. In the new **Create Hosted Zone** right hand pane, enter your noted domain name in for **Domain Name** and then click **Create**.

### Configure public access to OpenEMR

1. In the AWS Management Console, click **Services** and then **Route 53**.
2. In the left pane, click **Hosted zones**, then select your domain.
3. Click **Create Record Set** to the top.
4. In the new **Create Record Set** right pane, enter **www** for **Name** and **A - IPv4 address** for **Type**.
5. Checkbox **Yes** for **Alias**.
6. Under **Alias Target**, select the only entry under **Elastic Beanstalk Environments**.
7. Click **Create**.

### Associate Domain with a SSL Certificate

1. In the AWS Management Console, click **Services** and then **Certificate Manager**.
2. Click **Request a certificate** to the top left.
3. Under **Domain name**, enter your domain without a **www.** prefix.
4. Click **Add another name to this certificate**.
5. Enter another domain entry with a **\*.** prefix.
6. Click **Review and request**.
7. Click **Confirm and request**.
8. Wait around 10 minutes for multiple AWS emails to be sent to you. Follow the instructions that are sent in said emails.

### Configure Amazon Simple Email Service

1. In the AWS Management Console, click **Services** and then **SES**.
2. In the left pane, click **Domains**, then **Verify a New Domain** to the top.
3. Enter the domain (without any **www** but with the **.com** or similar extension), then click the **DKIM** box, and then click **Verify This Domain**.
4. Click **Route 53**.
5. Click **Create Record Sets**. As soon as Amazon sees the records on your domain, the SES verification process will complete.
6. In the left pane, click **SMTP Settings**. Note the **Server Name** in a safe place. Note that this name is probably **email-smtp.&lt;region&gt;.amazonaws.com**.
7. Click **Create My SMTP Credentials**, then **Create**, and then **Show User SMTP Security Credentials**.
8. Note the SMTP username and password in a safe place.
9. Login into OpenEMR using your new administrative credentials.
10. At the top, hover over **Administration** and then click **Globals**.
11. Now with the settings area in view, click the **Notifications** tab.
 * For the **Email Transport Method** field, select **SMTP**.
 * For the **SMTP Server Hostname** field, enter the recently noted value from step 6.
 * For the **SMTP Server Port Number** field, enter **587**.
 * For the **SMTP User for Authentication** field, enter the recently noted value from step 8.
 * For the **SMTP Security Protocol** field, select **TLS**.
12. Click **Save** near the bottom left.
