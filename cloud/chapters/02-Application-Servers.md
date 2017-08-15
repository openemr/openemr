_[< previous chapter](01-Getting-Started.md) | [next chapter >](03-Secure-Domain-Setup.md)_

# 🖥 Application Servers

### OpenEMR Setup

1. In the AWS Management Console, click **Services**, and then click **CloudFormation**.
2. Review the table and checkbox the row with **Stack Name** of **OpenEMR**.
3. With the bottom pane present and the **Overview** tab in focus, observe the **Status** value. Once the value is **CREATE_COMPLETE**, you may proceed.
4. Click the **Outputs** tab and click the link in the **URL** row.
5. Go through each step of the signup wizard.
   * Make sure you checkbox **I have already created the database** when asked.
   * The database password will be what you noted in the last chapter.
   * The MySQL host will be **mysql.openemr.local**.
   * Make sure to enter a [strong password](https://www.random.org/passwords/?num=1&len=16&format=html&rnd=new) for the initial admin user and record it in a safe place.
   * The first step of the wizard will take about 5 minutes. Although the page will be white and not have any loading indicators, please do not attempt to refresh the page or resubmit the request.

### Post-Installation Security Update

1. In the AWS Management Console, click **Services**, then **EC2**, then **Instances**.
2. Three instances have been created for this solution &mdash; "CouchDB Server", "EFS Backup Server", and a third one probably patterned something similar to "Open-EBEn-W3JASWI7XCC0". Checkbox that third server, then click "**Actions**", "**Instance State**", "**Terminate**", "**Yes, Terminate**".
3. In three to five minutes, a new instance will appear here with a similar name; once this instance's **Status Checks** read "2/2", you can proceed to the next step.

### Connect the Patient Documents Database

1. Login into OpenEMR using your new administrative credentials.
2. At the top, hover over **Administration** and then click **Globals**.
3. Now with the settings area in view, click the **Documents** tab.
 * For the **Document Storage Method** field, select **CouchDB**.
 * For the **CouchDB HostName** field, enter **couchdb.openemr.local**.
 * For the **CouchDB Database** field, enter **couchdb**.
 * For the **CouchDB Log Enable** field, checkbox the input.
4. Click **Save** near the bottom left.
