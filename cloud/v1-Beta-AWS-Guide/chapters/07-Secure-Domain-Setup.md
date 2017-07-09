_[< previous chapter](06-Application-Servers.md) | [next chapter >](08-CloudTrail.md)_

# ▶ Secure Domain Setup

### Purchase Custom Domain

1. In the AWS Management Console, click **Services** and then **Route 53**.
2. Under **Register domain**, enter your desired domain name and then click **Check**.
3. Assuming the domain is available, click **Add to cart** next to your desired domain name. Note the domain name in a safe place.
4. Scroll down to the bottom of the page and click **Continue**.
5. Enter your **Registrant**, **Administrative**, and **Technical** contact information and then click **Continue** at the bottom of the page.
6. Under **Terms and Conditions**, checkbox the agreement then click **Complete Purchase**.
7. Wait around 10 minutes for an AWS email to be sent to you entited **Verification of your contact data**. Click the confirmation link in the message body.

### Create Hosted Zone

1. In the AWS Management Console, click **Services** and then **Route 53**.
2. Click **Go To Domains** in the center of the screen.
3. In the left hand pane, click **Hosted zones**.
4. Click **Create Hosted Zone** to the top left.
5. Checkbox your recently created domain name and then click **Create Hosted Zone** to the top left.
6. In the new **Create Hosted Zone** right hand pane, enter your noted domain name in for **Domain Name** and then click **Create**.
7. Click **Create Record Set** to the top.
8. In the new **Create Record Set** right hand pane, enter "**www**" for **Name** and "**A - IPv4 address**" for **Type**.
9. Checkbox **"Yes"** for **Alias**.
10. Under **Alias Target**, select the only entry under **Elastic Beanstalk Environments**.
11. Click **Create**.

### Associate Domain with a SSL Certificate

1. In the AWS Management Console, click **Services** and then **Certificate Manager**.
2. Click **Request a certificate** to the top left.
3. Under **Domain name**, enter your domain without a "**www.**" prefix.
4. Click **Add another name to this certificate**.
5. Enter another domain entry with a **"*."** prefix.
6. Click **Review and request**.
7. Click **Confirm and request**.
8. Wait around 10 minutes for multiple AWS emails to be sent to you. Click the confirmation link in each message body.
9. Navigate to "**https://www.your_practice_domain.ext/openemr**" and inform your user base of the link.
