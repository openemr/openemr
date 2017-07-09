_[< previous chapter](01-Getting-Started.md) | [next chapter >](03-Network-File-System.md)_

# ☁ Private Cloud

### Lock down your system components with a private virtual network

1. In the AWS Management Console, click **Services** and then click **Start VPC Wizard**.
2. Click **VPC with a Single Public Subnet** and click **Select**.
3. In **VPC Name**, enter "**openemr-vpc**".
4. In **Availability Zone**, select your preferred zone. If you aren't sure, select the first entry. In fact, this is strongly encouraged due to issues with selecting entries at the bottom of the list which may not be supported in ElasticBeanstalk. Note this Availability Zone in a safe place.
5. For **Subnet name**, enter "**Public**".
6. Click **Create VPC**.
7. In the left pane, click **Subnets**, **Create Subnet** to the top.
8. For **Name tag**, enter "**Private**".
9. For **VPC**, select "**openemr-vpc**".
10. For **Availability Zone**, select the recently noted zone from step 4.
11. For **IPv4 CIDR block**, enter "**10.0.1.0/24**". Note this subnet, zone, and CIDR block in a safe place.
12. Click **Yes, Create**.
13. Click **Create Subnet** near the top.
14. For **Name tag**, enter "**Other Public**".
15. For **VPC**, select "**openemr-vpc**".
16. For **Availability Zone**, select a zone *other than* the recently noted zone from steps 4 and 10.
17. For **IPv4 CIDR block**, enter "**10.0.2.0/24**". Note this subnet, zone, and CIDR block in a safe place.
18. Click **Yes, Create**.
19. Once more, click **Create Subnet** near the top.
20. For **Name tag**, enter "**Other Private**".
21. For **VPC**, select "**openemr-vpc**".
22. For **Availability Zone**, select the zone that was recently noted zone in step 16.
23. For **IPv4 CIDR block**, enter "**10.0.3.0/24**". Note this subnet, zone, and CIDR block in a safe place.
24. Click **Yes, Create**.
25. In the left pane, click **Route Tables** and checkbox the route row for your VPC that is not the "**Main**" route.
26. In the bottom pane, click the **Subnet Associations** tab and observe that the "**Public**" subnet is already assigned to it in the first table.
27. Click **Edit** and checkbox the "**Other Public**" row.
28. Click **Save**.
29. In the left pane, click **NAT Gateways** and then click **Create NAT Gateway** to the top.
30. For **Subnet**, select the "**Public**" subnet.
31. Click **Create New EIP**, note the IP created in a safe place, then click **Create a NAT Gateway**.
32. When the **Your NAT Gateway has been created** dialog appears, note the **target** in a safe place and click **Edit Route Tables**.
33. Checkbox the route row for your VPC that is the "**Main**" route.
34. In the bottom pane, click the **Routes** tab, then **Edit**.
35. Click **Add another route**.
36. For **Destination**, enter "**0.0.0.0/0**".
37. For **Target**, select your recently noted NAT gateway target from step 32.
38. Click **Save**.

### Establish a VPN server for network

1. Click **Services**, **EC2**, and then **Launch Instance** in the center of the screen.
2. To the left, click **AWS Marketplace** and then search for "**OpenVPN**".
3. Locate "**OpenVPN Access Server"** and click **Select**.
4. For **Choose an Instance Type**, select "**t2.small"** and then click **Next: Configure Instance Details**.
5. For **Network**, select "**openemr-vpc**".
6. For **Subnet**, select "**Public**".
7. Checkbox **Protect against accidental termination**.
8. Click **Next: Add Storage**
9. Click **Next: Add Tags**
10. Click **Add Tag** to the left.
11. For **Key**, enter "**OpenEMR VPN**".
12. Click **Next: Configure Security Group**
13. For **Security group name**, enter "**VPN Server**".
14. Click **Review and Launch**.
15. When **Boot from General Purpose** dialog appears, cleckbox "**Continue with Magnetic as the boot volume for this instance**" and then click **Next**.
16. When **Select an existing key pair or create a new key pair** dialog shows up, select your key pair, accept the terms, and click **Launch Instances**.
17. Wait a few moments and then click **View Your Instances** to the bottom right.
18. Identify the recently created instance.
19. Click the icon in the **Name** column and name the instance "**openemr-vpn**".
20. In the left hand pane, click **Elastic IPs**.
21. Click **Allocate new address** to the top and then click **Allocate**.
22. When **A New address request succeeded** appears, note the Elastic IP in a safe place.
23. Click **Close**.
24. With the new address row checkboxed, click **Actions** and **Associate address**.
25. For **Instance**, select the only entry in the list.
26. Click **Associate** and then click **Close**.

### Configure VPN server for use

1. In the AWS Management Console, click **EC2** and then click **Running Instances**.
2. Checkbox the "**openemr-vpn**" instance.
3. Wait for the **Status Checks** column value to read "**2/2**".
4. In the bottom pane, note the **IPv4 Public IP** in a safe place.
5. To the top, click **Actions** and select **Networking**, **Change Security Groups**.
6. Checkbox the **default** security group row.
7. Click **Assign Security Groups**.
8. Using the IP noted from step 4, SSH into the server as "**openvpnas**". If you aren't sure, please review [How do I SSH into Instances](../chapters/09-Administration.md#how-do-i-ssh-into-instances) section.
9. Agree with the license, and press enter to all other answers to choose the defaults.
10. Wait a few moments.
11. Execute the command `sudo passwd openvpn`, set an administrative password and note it in a safe place.
12. Execute the command `exit` to disconnect from the SSH session.
13. Using a web browser, navigate to **https://&lt;&lt;recently noted ip from step 4&gt;&gt;:943/admin**. Note that you will be presented with a untrusted certificate warning, but it is safe to proceed.
14. At the **OpenVPN Admin Login** page, enter "**openvpn"** for **Username** and your recently noted password from step 11 for **Password**. Click **Sign In**.
15. Click **Agree** in the center of the screen.
16. Using a web browser, navigate to **https://&lt;&lt;recently noted ip from step 4&gt;&gt;:943/?src=connect** Note that you will be presented with a untrusted certificate warning, but it is safe to proceed.
17. Download and follow along with the OpenVPN wizard to install the connector software.
