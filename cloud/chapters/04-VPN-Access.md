_[< previous chapter](03-Secure-Domain-Setup.md) | [next chapter >](05-Administration.md)_

# 📝 VPN Access

You can use a VPN, or virtual private network, to tunnel into the Amazon VPC and connect directly to protected resources like the database and the application servers. However, you won't need to do so when you first create your stack &mdash; feel free to skip this for now and come back to it only after you need to explore or troubleshoot your OpenEMR installation.

### Establish a VPN Server for Private Cloud Access

1. Click **Services**, **EC2**, and then **Launch Instance** in the center of the screen.
2. To the left, click **AWS Marketplace** and then search for **OpenVPN**.
3. Locate **OpenVPN Access Server** and click **Select**.
4. For **Choose an Instance Type**, select **t2.small** and then click **Next: Configure Instance Details**.
5. For **Network**, select **openemr-vpc**.
6. For **Subnet**, select **Public**.
7. Checkbox **Protect against accidental termination**.
8. Click **Next: Add Storage**
9. Click **Next: Add Tags**
10. Click **Add Tag** to the left.
11. For **Key**, enter **OpenEMR VPN**.
12. Click **Next: Configure Security Group**
13. For **Security group name**, enter **VPN Server**.
14. Click **Review and Launch**.
15. When **Boot from General Purpose** dialog appears, checkbox **Continue with Magnetic as the boot volume for this instance** and then click **Next**.
16. When **Select an existing key pair or create a new key pair** dialog shows up, select your key pair, accept the terms, and click **Launch Instances**.
17. Wait a few moments and then click **View Your Instances** to the bottom right.
18. Identify the recently created instance, which will not have a name.
19. Click the icon in the **Name** column and name the instance "**openemr-vpn**".
20. In the left pane, click **Elastic IPs**.
21. Click **Allocate new address** to the top and then click **Allocate**.
22. When **A New address request succeeded** appears, note the Elastic IP in a safe place.
23. Click **Close**.
24. With the new address row checkboxed, click **Actions** and **Associate address**.
25. For **Instance**, select the **openemr-vpn** instance.
26. Click **Associate** and then click **Close**.

### Configure VPN Server

1. In the AWS Management Console, click **EC2** and then click **Running Instances**.
2. Checkbox the **openemr-vpn** instance.
3. Wait for the **Status Checks** column value to read **2/2**.
4. In the bottom pane, note the **IPv4 Public IP** in a safe place.
5. To the top, click **Actions** and select **Networking**, **Change Security Groups**.
6. Checkbox the **Application** security group row, and the one named "**awseb...**" that is described "**VPC Security Group**". Make sure to leave the existing security group checked!
7. Click **Assign Security Groups**.
8. Using the IP noted from step 4, SSH into the server as **openvpnas**. If you aren't sure, please review [How do I SSH into Instances](../chapters/05-Administration.md#how-do-i-ssh-into-instances) section.
9. Agree with the license, and press enter to all other answers to choose the defaults.
10. Wait a few moments.
11. Execute the command `sudo passwd openvpn`, set an administrative password and note it in a safe place.
12. Execute the command `exit` to disconnect from the SSH session.
13. Using a web browser, navigate to **https://&lt;&lt;recently noted ip from step 4&gt;&gt;:943/admin**. Note that you will be presented with a untrusted certificate warning, but it is safe to proceed.
14. At the **OpenVPN Admin Login** page, enter **openvpn** for **Username** and your recently noted password from step 11 for **Password**. Click **Sign In**.
15. Click **Agree** in the center of the screen.
16. Using a web browser, navigate to **https://&lt;&lt;recently noted ip from step 4&gt;&gt;:943/?src=connect** Note that you will be presented with a untrusted certificate warning, but it is safe to proceed.
17. Download and follow along with the OpenVPN wizard to install the connector software.
