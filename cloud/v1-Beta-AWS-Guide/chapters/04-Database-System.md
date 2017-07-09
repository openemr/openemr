_[< previous chapter](03-Network-File-System.md) | [next chapter >](05-Session-Management.md)_

# 💽 Database System

### Prepare network configuration

1. In the AWS Management Console, click **Services** and then click **RDS**.
2. In the left hand pane, click **Subnet Groups**, then **Create DB Subnet Group** to the top.
3. For **Name**, enter "**openemr-db-subnets**".
4. For **Description**, enter "**OpenEMR DB Subnets**".
5. For **VPC**, select "**openemr-vpc**".
6. For **Availability Zone**, select the zone that corresponds to the **10.0.1.0/24** subnet that was noted in chapter 2.
7. For **Subnet ID**, select "**10.0.1.0/24**", then click **Add**.
8. Once more, for **Availability Zone**, select the zone that corresponds to the **10.0.2.0/24** subnet that was noted in chapter 2.
9. For **Subnet ID**, select "**10.0.2.0/24**", then click **Add**.
10. Click **Create**.

### Create a fully managed MySQL database

1. In the AWS Management Console, click **Services** and then click **RDS**.
2. Under **Create Instance**, click **Launch a DB Instance**. If you are using a brand new account, you'll have to click **Instances** in the left hand pane to get around the marketing screen.
3. Select **MySQL** and click **MySQL**.
4. Click **Select**.
5. Under **Production**, click **MySQL**.
6. Click **Next Step**.
7. Apply the following under **Instance Specifications**:
    1. In **DB Engine Version**, select "**MySQL 5.6.27**".
    2. In **DB Instance Class**, select your preferred instance size. If you aren't sure, select "**db.t2.large**".
    3. In **Select Multi-AZ Deployment**, select your preferred AZ configuration. If you aren't sure, select "**No**".
    4. In **Storage Type**, select "**General Purpose (SSD)**".
    5. In **Allocated Storage**, select your preferred size. If you aren't sure, enter "**500GB**".
8. Apply the following under **Settings**:
    1. In **DB Instance Identifier**, enter "**openemr**".
    2. In **Master User**, enter "**openemr_db_user**".
    3. In **Master Password**, enter a [strong password](https://www.random.org/passwords/?num=1&len=16&format=html&rnd=new). Make sure this is recorded in a safe place.
9. Click **Next Step**.

### Restrict database access to its own private network

1. Apply the following under **Network & Security**
    1. In **VPC**, select "**openemr-vpc**".
    2. In **Subnet Group**, select "**openemr-db-subnets**".
    3. In **Publicly Accessible**, select "**No**".
    4. In **Availability Zone**, select your preferred zone. If you aren't sure, select "**No Preference**".
    5. In **VPC Security Group(s)**, select "**default**".
2. Apply the following under **Database Options**:
    1. In **Database Name**, enter "**openemr**".
    2. In **Database Port**, enter "**3306**".
    3. In **DB Parameter Group**, select "**default.mysql5.6**".
    4. In **Option Group**, select "**default:mysql-5-6**".
    5. In **Copy Tags To Snapshots**, uncheck box.

### Setup a data backup strategy

1. Apply the following under **Backup**:
    1. In **Backup Retention Period**, select your preferred days. If you aren't sure, select "**7**".
    2. In **Backup Window**, select "**Select Window**" and choose your preferred window. If you aren't sure, select "**00:00**".

### Allow for system health checks

1. Apply the following under **Monitoring**:
    1. In **Enable Enhanced Monitoring**, select "**Yes**".
    2. In **Monitoring Role**, select "**Default**".
    3. In **Granularity**, select your preferred second(s). If you aren't sure, select "**60**".

### Permit minor safety updates to your database engine

1. Apply the following under **Maintenance**:
    1. In **Auto Minor Version Upgrade**, select your preferred strategy. If you aren't sure, select "**Yes**".
    2. In **Maintenance Window**, and choose your preferred window. If you aren't sure, select "**00:00**".

### Launch your fully configured database

1. Click **Launch DB Instance**.
2. Click **View Your DB Instances**.
3. Wait many moments for the database to be created.
4. Click on the first row of the **Instances** table.
5. Record the **Endpoint** (without the ":3306" section) in a safe place.
