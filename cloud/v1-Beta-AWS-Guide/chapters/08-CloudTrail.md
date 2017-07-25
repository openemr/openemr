_[< previous chapter](07-Secure-Domain-Setup.md) | [next chapter >](09-Administration.md)_

# 📝 CloudTrail

_CloudFormation is used for setting up CloudTrail. Once this chapter is complete, you will have a new S3 bucket in which CloudTrail will store the logs._

### Set up CloudTrail to provide an auditable history of API calls for compliance and security analysis.

1. Download the **CloudTrail-CF.json** file [here](../assets/cf) in your local **openemr** folder. Be careful to save the file as `.json` and not `.json.txt`.
2. In the AWS Management Console, click **Services** and then click **CloudFormation**.
3. Click **Create Stack**.
4. In the **Choose a template** section, click the **upload file** button and select the **CloudTrail-CF.json** file downloaded in step 1.
5. Click **Next**.
6. For **Stack name**, enter "**CloudTrail-CF**".
7. Click **Next**.
8. Click **Next** again.
9. Click **Create** and wait for the stack to finish creating.
10. Once the stack has a status of **CREATE_COMPLETE**, click on **Services** and then click **S3**.
11. Find the new bucket that CloudFormation created. The bucket will have a name with this format: **\<_your account ID_\>-cloudtrail-logs**.
12. Click into the bucket, then **AWSLogs**, then **\<_your account ID_\>**, then **CloudTrail**.
13. Here, CloudTrail will store your AWS activity with a hierarchy of **region/year/month/day**. The data saved in these logs will be useful to administrators and auditors. Note this location in a safe place.
