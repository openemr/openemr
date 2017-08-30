#!/usr/bin/python
# -*- coding: utf-8 -*-

# TODO: fix timezone -- where is it?! [ready, rezip and repush]
# TODO: force two-way replication of views (or do I /need/ them?)

from troposphere import Base64, FindInMap, GetAtt, GetAZs, Join, Select, Split, Output
from troposphere import Parameter, Ref, Tags, Template
from troposphere import ec2, route53, kms, s3, efs, elasticache, cloudtrail, rds, iam, cloudformation, awslambda, events, elasticbeanstalk

import argparse

ref_stack_id = Ref('AWS::StackId')
ref_region = Ref('AWS::Region')
ref_stack_name = Ref('AWS::StackName')
ref_account = Ref('AWS::AccountId')

currentBeanstalkKey = 'beanstalk/openemr-5.0.0-007.zip'

def setInputs(t, args):
    t.add_parameter(Parameter(
        'EC2KeyPair',
        Description = 'Amazon EC2 Key Pair',
        Type = 'AWS::EC2::KeyPair::KeyName'
    ))

    if (args.recovery):
        t.add_parameter(Parameter(
            'RecoveryKMSKey',
            Description = 'The KMS key ARN for the previous stack (''arn:aws:kms...'')',
            Type = 'String'
        ))
        t.add_parameter(Parameter(
            'RecoveryRDSSnapshotARN',
            Description = 'The database snapshot ARN for the previous stack (''arn:aws:rds...'')',
            Type = 'String'
        ))
        t.add_parameter(Parameter(
            'RecoveryCouchDBSnapshot',
            Description = 'The document store snapshot ID for the previous stack',
            Type = 'String'
        ))
        t.add_parameter(Parameter(
            'RecoveryS3Bucket',
            Description = 'The S3 bucket for the previous stack',
            Type = 'String'
        ))
    else:
        t.add_parameter(Parameter(
            'TimeZone',
            Description = 'The timezone OpenEMR will run in',
            Default = 'America/Chicago',
            Type = 'String',
            MaxLength = '41'
        ))

        t.add_parameter(Parameter(
            'RDSPassword',
            NoEcho = True,
            Description = 'The database admin account password',
            Type = 'String',
            MinLength = '8',
            MaxLength = '41'
        ))

        t.add_parameter(Parameter(
            'PatientRecords',
            Description = 'Database storage for patient records (minimum 10 GB)',
            Default = '10',
            Type = 'Number',
            MinValue = '10'
        ))

        t.add_parameter(Parameter(
            'DocumentStorage',
            Description = 'Document database for patient documents (minimum 500 GB)',
            Default = '500',
            Type = 'Number',
            MinValue = '10'
        ))
    return t

def setMappings(t, args):
    t.add_mapping('RegionData', {
        "us-east-1" : {
            "RegionBucket": "openemr-useast1",
            "ApplicationSource": args.beanstalk_key,
            "MySQLVersion": "5.6.27",
            "AmazonAMI": "ami-a4c7edb2",
            "UbuntuAMI": "ami-d15a75c7"
        },
        "us-west-2" : {
            "RegionBucket": "openemr-uswest2",
            "ApplicationSource": args.beanstalk_key,
            "MySQLVersion": "5.6.27",
            "AmazonAMI": "ami-6df1e514",
            "UbuntuAMI": "ami-835b4efa"
        },
        "eu-west-1" : {
            "RegionBucket": "openemr-euwest1",
            "ApplicationSource": args.beanstalk_key,
            "MySQLVersion": "5.6.27",
            "AmazonAMI": "ami-d7b9a2b1",
            "UbuntuAMI": "ami-6d48500b"
        },
        "ap-southeast-2" : {
            "RegionBucket": "openemr-apsoutheast2",
            "ApplicationSource": args.beanstalk_key,
            "MySQLVersion": "5.6.27",
            "AmazonAMI": "ami-10918173",
            "UbuntuAMI": "ami-e94e5e8a"
        }
    })
    return t

def buildVPC(t, dual_az):
    t.add_resource(
        ec2.VPC(
            'VPC',
            CidrBlock='10.0.0.0/16',
            EnableDnsSupport='true',
            EnableDnsHostnames='true'
        )
    )

    t.add_resource(
        ec2.Subnet(
            'PublicSubnet1',
            VpcId = Ref('VPC'),
            CidrBlock = '10.0.1.0/24',
            AvailabilityZone = Select("0", GetAZs(""))
        )
    )

    t.add_resource(
        ec2.Subnet(
            'PrivateSubnet1',
            VpcId = Ref('VPC'),
            CidrBlock = '10.0.2.0/24',
            AvailabilityZone = Select("0", GetAZs(""))
        )
    )

    t.add_resource(
        ec2.Subnet(
            'PublicSubnet2',
            VpcId = Ref('VPC'),
            CidrBlock = '10.0.3.0/24',
            AvailabilityZone = Select("1", GetAZs(""))
        )
    )

    t.add_resource(
        ec2.Subnet(
            'PrivateSubnet2',
            VpcId = Ref('VPC'),
            CidrBlock = '10.0.4.0/24',
            AvailabilityZone = Select("1", GetAZs(""))
        )
    )

    t.add_resource(
        ec2.InternetGateway(
            'ig'
        )
    )

    t.add_resource(
        ec2.VPCGatewayAttachment(
            'igAttach',
            VpcId = Ref('VPC'),
            InternetGatewayId = Ref('ig')
        )
    )

    t.add_resource(
        ec2.RouteTable(
            'rtTablePublic',
            VpcId = Ref('VPC')
        )
    )

    t.add_resource(
        ec2.Route(
            'rtPublic',
            RouteTableId = Ref('rtTablePublic'),
            DestinationCidrBlock = '0.0.0.0/0',
            GatewayId = Ref('ig'),
            DependsOn = 'igAttach'
        )
    )

    t.add_resource(
        ec2.SubnetRouteTableAssociation(
            'rtPublic1Attach',
            SubnetId = Ref('PublicSubnet1'),
            RouteTableId = Ref('rtTablePublic')
        )
    )

    t.add_resource(
        ec2.SubnetRouteTableAssociation(
            'rtPublic2Attach',
            SubnetId = Ref('PublicSubnet2'),
            RouteTableId = Ref('rtTablePublic')
        )
    )

    if (dual_az):
        t.add_resource(
            ec2.RouteTable(
                'rtTablePrivate1',
                VpcId = Ref('VPC')
            )
        )

        t.add_resource(
            ec2.EIP(
                'natIp1',
                Domain = 'vpc'
            )
        )

        t.add_resource(
            ec2.NatGateway(
                'nat1',
                AllocationId = GetAtt('natIp1', 'AllocationId'),
                SubnetId = Ref('PublicSubnet1')
            )
        )

        t.add_resource(
            ec2.Route(
                'rtPrivate1',
                RouteTableId = Ref('rtTablePrivate1'),
                DestinationCidrBlock = '0.0.0.0/0',
                NatGatewayId = Ref('nat1')
            )
        )

        t.add_resource(
            ec2.SubnetRouteTableAssociation(
                'rtPrivate1Attach',
                SubnetId = Ref('PrivateSubnet1'),
                RouteTableId = Ref('rtTablePrivate1')
            )
        )

        t.add_resource(
            ec2.RouteTable(
                'rtTablePrivate2',
                VpcId = Ref('VPC')
            )
        )

        t.add_resource(
            ec2.EIP(
                'natIp2',
                Domain = 'vpc'
            )
        )

        t.add_resource(
            ec2.NatGateway(
                'nat2',
                AllocationId = GetAtt('natIp2', 'AllocationId'),
                SubnetId = Ref('PublicSubnet2')
            )
        )

        t.add_resource(
            ec2.Route(
                'rtPrivate2',
                RouteTableId = Ref('rtTablePrivate2'),
                DestinationCidrBlock = '0.0.0.0/0',
                NatGatewayId = Ref('nat2')
            )
        )

        t.add_resource(
            ec2.SubnetRouteTableAssociation(
                'rtPrivate2Attach',
                SubnetId = Ref('PrivateSubnet2'),
                RouteTableId = Ref('rtTablePrivate2')
            )
        )
    else:
        t.add_resource(
            ec2.RouteTable(
                'rtTablePrivate',
                VpcId = Ref('VPC')
            )
        )

        t.add_resource(
            ec2.EIP(
                'natIp',
                Domain = 'vpc'
            )
        )

        t.add_resource(
            ec2.NatGateway(
                'nat',
                AllocationId = GetAtt('natIp', 'AllocationId'),
                SubnetId = Ref('PublicSubnet1')
            )
        )

        t.add_resource(
            ec2.Route(
                'rtPrivate',
                RouteTableId = Ref('rtTablePrivate'),
                DestinationCidrBlock = '0.0.0.0/0',
                NatGatewayId = Ref('nat')
            )
        )

        t.add_resource(
            ec2.SubnetRouteTableAssociation(
                'rtPrivate1Attach',
                SubnetId = Ref('PrivateSubnet1'),
                RouteTableId = Ref('rtTablePrivate')
            )
        )

        t.add_resource(
            ec2.SubnetRouteTableAssociation(
                'rtPrivate2Attach',
                SubnetId = Ref('PrivateSubnet2'),
                RouteTableId = Ref('rtTablePrivate')
            )
        )

    return t

def buildFoundation(t, args):

    t.add_resource(
        route53.HostedZone(
            'DNS',
            Name='openemr.local',
            VPCs = [route53.HostedZoneVPCs(
                VPCId = Ref('VPC'),
                VPCRegion = ref_region
            )]
        )
    )

    if (not args.recovery):
        t.add_resource(
            kms.Key(
                'OpenEMRKey',
                DeletionPolicy = 'Delete' if args.dev else 'Retain',
                KeyPolicy = {
                    "Version": "2012-10-17",
                    "Id": "key-default-1",
                    "Statement": [{
                        "Sid": "1",
                        "Effect": "Allow",
                        "Principal": {
                            "AWS": [
                                Join(':', ['arn:aws:iam:', ref_account, 'root'])
                            ]
                        },
                        "Action": "kms:*",
                        "Resource": "*"
                    }]
                }
            )
        )

    t.add_resource(
        s3.Bucket(
            'S3Bucket',
            DeletionPolicy = 'Retain',
            BucketName = Join('-', ['openemr', Select('2', Split('/', ref_stack_id))])
        )
    )

    t.add_resource(
        s3.BucketPolicy(
            'BucketPolicy',
            Bucket = Ref('S3Bucket'),
            PolicyDocument = {
                "Version": "2012-10-17",
                "Statement": [
                    {
                      "Sid": "AWSCloudTrailAclCheck",
                      "Effect": "Allow",
                      "Principal": { "Service":"cloudtrail.amazonaws.com"},
                      "Action": "s3:GetBucketAcl",
                      "Resource": { "Fn::Join" : ["", ["arn:aws:s3:::", {"Ref":"S3Bucket"}]]}
                    },
                    {
                      "Sid": "AWSCloudTrailWrite",
                      "Effect": "Allow",
                      "Principal": { "Service":"cloudtrail.amazonaws.com"},
                      "Action": "s3:PutObject",
                      "Resource": { "Fn::Join" : ["", ["arn:aws:s3:::", {"Ref":"S3Bucket"}, "/AWSLogs/", {"Ref":"AWS::AccountId"}, "/*"]]},
                      "Condition": {
                        "StringEquals": {
                          "s3:x-amz-acl": "bucket-owner-full-control"
                        }
                      }
                    }
                ]
            }
        )
    )

    t.add_resource(
        cloudtrail.Trail(
            'CloudTrail',
            DependsOn = 'BucketPolicy',
            IsLogging = True,
            IncludeGlobalServiceEvents = True,
            IsMultiRegionTrail = True,
            S3BucketName = Ref('S3Bucket')
        )
    )

    t.add_resource(
        ec2.SecurityGroup(
            'ApplicationSecurityGroup',
            GroupDescription = 'Application Security Group',
            VpcId = Ref('VPC'),
            Tags = [ { "Key" : "Name", "Value" : "Application" } ]
        )
    )

    t.add_resource(
        ec2.SecurityGroupIngress(
            'AppSGIngress',
            GroupId = Ref('ApplicationSecurityGroup'),
            IpProtocol = '-1',
            SourceSecurityGroupId = Ref('ApplicationSecurityGroup')
        )
    )

    return t

def buildDeveloperBastion(t):

    t.add_resource(
        ec2.SecurityGroup(
            'SSHSecurityGroup',
            GroupDescription = 'insecure worldwide SSH access',
            VpcId = Ref('VPC'),
            Tags = [ { "Key" : "Name", "Value" : "Global SSH" } ]
        )
    )

    t.add_resource(
        ec2.SecurityGroupIngress(
            'SSHSGIngress',
            GroupId = Ref('SSHSecurityGroup'),
            IpProtocol = 'tcp',
            CidrIp = '0.0.0.0/0',
            FromPort = '22',
            ToPort = '22'
        )
    )

    t.add_resource(
        ec2.Instance(
            'DeveloperBastion',
            ImageId = FindInMap('RegionData', ref_region, 'AmazonAMI'),
            InstanceType = 't2.nano',
            KeyName = Ref('EC2KeyPair'),
            Tags=Tags(Name="Developer Bastion"),
            NetworkInterfaces = [ec2.NetworkInterfaceProperty(
                AssociatePublicIpAddress = True,
                DeviceIndex = "0",
                GroupSet = [ Ref('SSHSecurityGroup'), Ref('ApplicationSecurityGroup') ],
                SubnetId = Ref('PublicSubnet2')
            )]
        )
    )

    t.add_output(
        Output(
            'DeveloperKeyhole',
            Description='direct stack access',
            Value=GetAtt('DeveloperBastion', 'PublicIp')
        )
    )

    return t

def buildEFS(t, dev):
    t.add_resource(
        ec2.SecurityGroup(
            'EFSSecurityGroup',
            GroupDescription = 'Webworker NFS Access',
            VpcId = Ref('VPC'),
            Tags = Tags(Name='NFS Access')
        )
    )

    t.add_resource(
        ec2.SecurityGroupIngress(
            'EFSSGIngress',
            GroupId = Ref('EFSSecurityGroup'),
            IpProtocol = '-1',
            SourceSecurityGroupId = Ref('ApplicationSecurityGroup')
        )
    )

    t.add_resource(
        efs.FileSystem(
            'ElasticFileSystem',
            DeletionPolicy = 'Delete' if dev else 'Retain',
            FileSystemTags = Tags(Name='OpenEMR Codebase')
        )
    )

    t.add_resource(
        efs.MountTarget(
            'EFSMountPrivate1',
            FileSystemId = Ref('ElasticFileSystem'),
            SubnetId = Ref('PrivateSubnet1'),
            SecurityGroups = [Ref('EFSSecurityGroup')]
        )
    )

    t.add_resource(
        efs.MountTarget(
            'EFSMountPrivate2',
            FileSystemId = Ref('ElasticFileSystem'),
            SubnetId = Ref('PrivateSubnet2'),
            SecurityGroups = [Ref('EFSSecurityGroup')]
        )
    )

    t.add_resource(
        route53.RecordSetType(
            'DNSEFS',
            DependsOn = ['EFSMountPrivate1', 'EFSMountPrivate2'],
            HostedZoneId = Ref('DNS'),
            Name = 'nfs.openemr.local',
            Type = 'CNAME',
            TTL = '900',
            ResourceRecords = [Join("", [Ref('ElasticFileSystem'), '.efs.', ref_region, ".amazonaws.com"])]
        )
    )

    return t

def buildRedis(t, dual_az):
    t.add_resource(
        ec2.SecurityGroup(
            'RedisSecurityGroup',
            GroupDescription = 'Webworker Session Store',
            VpcId = Ref('VPC'),
            Tags = Tags(Name='Redis Access')
        )
    )

    t.add_resource(
        ec2.SecurityGroupIngress(
            'RedisSGIngress',
            GroupId = Ref('RedisSecurityGroup'),
            IpProtocol = '-1',
            SourceSecurityGroupId = Ref('ApplicationSecurityGroup')
        )
    )

    t.add_resource(
        elasticache.SubnetGroup(
            'RedisSubnets',
            Description = 'Redis node locations',
            SubnetIds = [Ref('PrivateSubnet1'), Ref('PrivateSubnet2')]
        )
    )

    if (dual_az):
        t.add_resource(
            elasticache.ReplicationGroup(
                'RedisCluster',
                AutomaticFailoverEnabled = True,
                ReplicationGroupDescription = 'Beanstalk Sessions',
                NumCacheClusters = 2,
                Engine = 'redis',
                CacheNodeType = 'cache.m3.medium',
                CacheSubnetGroupName = Ref('RedisSubnets'),
                SecurityGroupIds = [GetAtt('RedisSecurityGroup', 'GroupId')],
            )
        )
        t.add_resource(
            route53.RecordSetType(
                'DNSRedis',
                HostedZoneId = Ref('DNS'),
                Name = 'redis.openemr.local',
                Type = 'CNAME',
                TTL = '900',
                ResourceRecords = [GetAtt('RedisCluster', 'PrimaryEndPoint.Address')]
            )
        )
    else:
        t.add_resource(
            elasticache.CacheCluster(
                'RedisCluster',
                CacheNodeType = 'cache.t2.small',
                VpcSecurityGroupIds = [GetAtt('RedisSecurityGroup', 'GroupId')],
                CacheSubnetGroupName = Ref('RedisSubnets'),
                Engine = 'redis',
                NumCacheNodes = 1
            )
        )
        t.add_resource(
            route53.RecordSetType(
                'DNSRedis',
                HostedZoneId = Ref('DNS'),
                Name = 'redis.openemr.local',
                Type = 'CNAME',
                TTL = '900',
                ResourceRecords = [GetAtt('RedisCluster', 'RedisEndpoint.Address')]
            )
        )

    return t

def buildMySQL(t, args):
    t.add_resource(
        ec2.SecurityGroup(
            'DBSecurityGroup',
            GroupDescription = 'Patient Records',
            VpcId = Ref('VPC'),
            Tags = Tags(Name='MySQL Access')
        )
    )

    t.add_resource(
        ec2.SecurityGroupIngress(
            'DBSGIngress',
            GroupId = Ref('DBSecurityGroup'),
            IpProtocol = '-1',
            SourceSecurityGroupId = Ref('ApplicationSecurityGroup')
        )
    )

    t.add_resource(
        rds.DBSubnetGroup(
            'RDSSubnetGroup',
            DBSubnetGroupDescription = 'MySQL node locations',
            SubnetIds = [Ref('PrivateSubnet1'), Ref('PrivateSubnet2')]
        )
    )

    if (args.recovery):
        t.add_resource(
            rds.DBInstance(
                'RDSInstance',
                DeletionPolicy = 'Delete' if args.dev else 'Snapshot',
                DBSnapshotIdentifier = Ref('RecoveryRDSSnapshotARN'),
                DBInstanceClass = 'db.t2.small',
                PubliclyAccessible = False,
                DBSubnetGroupName = Ref('RDSSubnetGroup'),
                VPCSecurityGroups = [Ref('DBSecurityGroup')],
                MultiAZ = args.dual_az,
                Tags = Tags(Name='Patient Records')
            )
        )
    else:
        t.add_resource(
            rds.DBInstance(
                'RDSInstance',
                DeletionPolicy = 'Delete' if args.dev else 'Snapshot',
                DBName = 'openemr',
                AllocatedStorage = Ref('PatientRecords'),
                DBInstanceClass = 'db.t2.small',
                Engine = 'MySQL',
                EngineVersion = FindInMap('RegionData', ref_region, 'MySQLVersion'),
                MasterUsername = 'openemr',
                MasterUserPassword = Ref('RDSPassword'),
                PubliclyAccessible = False,
                DBSubnetGroupName = Ref('RDSSubnetGroup'),
                VPCSecurityGroups = [Ref('DBSecurityGroup')],
                KmsKeyId = OpenEMRKeyID,
                StorageEncrypted = True,
                MultiAZ = args.dual_az,
                Tags = Tags(Name='Patient Records')
            )
        )

    t.add_resource(
        route53.RecordSetType(
            'DNSMySQL',
            HostedZoneId = Ref('DNS'),
            Name = 'mysql.openemr.local',
            Type = 'CNAME',
            TTL = '900',
            ResourceRecords = [GetAtt('RDSInstance', 'Endpoint.Address')]
        )
    )

    return t

def buildCertWriter(t, dev):
    t.add_resource(
        iam.ManagedPolicy(
            'CertWriterPolicy',
            Description='Policy for initial CA writer',
            PolicyDocument = {
                "Version": "2012-10-17",
                "Statement": [
                {
                  "Sid": "Stmt1500612724000",
                  "Effect": "Allow",
                  "Action": [
                      "s3:*"
                  ],
                  "Resource": [
                    Join('', ['arn:aws:s3:::', Ref('S3Bucket'), "/CA/*"])
                  ]
                },
                {
                  "Sid": "Stmt1500612724001",
                  "Effect": "Allow",
                  "Action": [
                      "s3:ListBucket"
                  ],
                  "Resource": [
                      Join('', ['arn:aws:s3:::', Ref('S3Bucket')])
                  ]
                },
                {
                  "Sid": "Stmt1500612724002",
                  "Effect": "Allow",
                  "Action": [
                      "kms:GenerateDataKey*"
                  ],
                  "Resource": [
                    OpenEMRKeyARN
                  ]
                }
                ]
            }
        )
    )

    t.add_resource(
        iam.Role(
            'CertWriterRole',
            AssumeRolePolicyDocument = {
               "Version" : "2012-10-17",
               "Statement": [ {
                  "Effect": "Allow",
                  "Principal": {
                     "Service": [ "ec2.amazonaws.com" ]
                  },
                  "Action": [ "sts:AssumeRole" ]
               } ]
            },
            Path='/',
            ManagedPolicyArns= [Ref('CertWriterPolicy')]
        )
    )

    t.add_resource(
        iam.InstanceProfile(
            'CertWriterInstanceProfile',
            Path = '/',
            Roles = [Ref('CertWriterRole')]
        )
    )

    instanceScript = [
        "#!/bin/bash -xe\n",
        "cd /root\n",
        "mkdir -m 700 CA CA/certs CA/keys CA/work\n",
        "cd CA\n",
        "openssl genrsa -out keys/ca.key 8192\n",
        "openssl req -new -x509 -extensions v3_ca -key keys/ca.key -out certs/ca.crt -days 3650 -subj '/CN=OpenEMR Backend CA'\n",
        "openssl req -new -nodes -newkey rsa:2048 -keyout keys/beanstalk.key -out work/beanstalk.csr -days 3648 -subj /CN=beanstalk.openemr.local\n",
        "openssl x509 -req -in work/beanstalk.csr -out certs/beanstalk.crt -CA certs/ca.crt -CAkey keys/ca.key -CAcreateserial\n",
        "openssl req -new -nodes -newkey rsa:2048 -keyout keys/couch.key -out work/couch.csr -days 3648 -subj /CN=couchdb.openemr.local\n",
        "openssl x509 -req -in work/couch.csr -out certs/couch.crt -CA certs/ca.crt -CAkey keys/ca.key\n",
        "aws s3 sync keys s3://", Ref('S3Bucket'), "/CA/keys --sse aws:kms --sse-kms-key-id ", OpenEMRKeyID, " --acl private\n",
        "aws s3 sync certs s3://", Ref('S3Bucket'), "/CA/certs --acl public-read\n",
        "/opt/aws/bin/cfn-signal -e 0 ",
        "         --stack ", ref_stack_name,
        "         --resource CertWriterInstance ",
        "         --region ", ref_region, "\n",
        "shutdown -h now", "\n"
    ]

    t.add_resource(
        ec2.Instance(
            'CertWriterInstance',
            DependsOn = 'rtPrivate1Attach',
            ImageId = FindInMap('RegionData', ref_region, 'AmazonAMI'),
            InstanceType = 't2.nano',
            SubnetId = Ref('PrivateSubnet1'),
            KeyName = Ref('EC2KeyPair'),
            IamInstanceProfile = Ref('CertWriterInstanceProfile'),
            Tags = Tags(Name='Backend CA Processor'),
            InstanceInitiatedShutdownBehavior = 'stop' if args.dev else 'terminate',
            UserData = Base64(Join('', instanceScript)),
            CreationPolicy = {
              "ResourceSignal" : {
                "Timeout" : "PT5M"
              }
            }
        )
    )

    t.add_resource(
        iam.Role(
            'BarebonesLambdaRole',
            AssumeRolePolicyDocument = {
               "Version" : "2012-10-17",
               "Statement": [ {
                  "Effect": "Allow",
                  "Principal": {
                     "Service": [ "lambda.amazonaws.com" ]
                  },
                  "Action": [ "sts:AssumeRole" ]
               } ]
            },
            Path='/',
            Policies=[iam.Policy(
                PolicyName="root",
                PolicyDocument= {
                    "Version": "2012-10-17",
                    "Statement": [
                      { "Effect": "Allow", "Action": ["logs:*"], "Resource": "arn:aws:logs:*:*:*" },
                    ]
                }
            )]
        )
    )

    certGrabberScript = [
        "import urllib2",
        "import json",
        "def lambda_handler(event, context):",
        "  if (event['RequestType'] == 'Delete'):",
        "    sendResponse(event, context, 'SUCCESS', None)",
        "    return",
        "  sendResponse(event, context, 'SUCCESS', urllib2.urlopen(event['ResourceProperties']['Url']).read()[28:-27])",
        "def sendResponse(event, context, responseStatus, responseData):",
        "  opener = urllib2.build_opener(urllib2.HTTPHandler)",
        "  o = {}",
        "  o['Status'] = responseStatus",
        "  o['Reason'] = 'log ' + context.log_stream_name",
        "  o['PhysicalResourceId'] = context.log_stream_name",
        "  o['StackId'] = event['StackId']",
        "  o['RequestId'] = event['RequestId']",
        "  o['LogicalResourceId'] = event['LogicalResourceId']",
        "  o['Data'] = {'PublicKey': responseData}",
        "  r = json.dumps(o)",
        "  request = urllib2.Request(event['ResponseURL'], data=r)",
        "  request.add_header('Content-Type', '')",
        "  request.add_header('Content-Length', len(r))",
        "  request.get_method = lambda: 'PUT'",
        "  url = opener.open(request)"
    ]

    t.add_resource(
        awslambda.Function(
            'CertGrabberFunction',
            Description='gets a certificate''s embedded key',
            Handler='index.lambda_handler',
            Role=GetAtt('BarebonesLambdaRole','Arn'),
            Code=awslambda.Code(
                ZipFile=Join('\n', certGrabberScript)
            ),
            Runtime="python2.7",
            Timeout="5"
        )
    )

    t.add_resource(
        cloudformation.CustomResource(
            'EBCert',
            DependsOn='CertWriterInstance',
            ServiceToken = GetAtt('CertGrabberFunction', 'Arn'),
            Url=Join('', ['https://', Ref('S3Bucket'), '.s3.amazonaws.com/CA/certs/beanstalk.crt'])
        )
    )

    return t

def buildNFSBackup(t, args):
    t.add_resource(
        ec2.SecurityGroup(
            'NFSBackupSecurityGroup',
            GroupDescription = 'NFS Backup Access',
            VpcId = Ref('VPC'),
            Tags = Tags(Name='NFS Backup Access')
        )
    )

    t.add_resource(
        ec2.SecurityGroupIngress(
            'NFSSGIngress',
            GroupId = Ref('EFSSecurityGroup'),
            IpProtocol = '-1',
            SourceSecurityGroupId = Ref('NFSBackupSecurityGroup')
        )
    )

    if (args.dev or args.force_bastion):
        t.add_resource(
            ec2.SecurityGroupIngress(
                'NFSBackupSGIngress',
                GroupId = Ref('NFSBackupSecurityGroup'),
                IpProtocol = '-1',
                SourceSecurityGroupId = Ref('SSHSecurityGroup')
            )
        )

    rolePolicyStatements = [
        {
          "Sid": "Stmt1500699052003",
          "Effect": "Allow",
          "Action": ["s3:ListBucket"],
          "Resource" : [Join("", ["arn:aws:s3:::", Ref('S3Bucket')])]
        },
        {
            "Sid": "Stmt1500699052000",
            "Effect": "Allow",
            "Action": [
              "s3:PutObject",
              "s3:GetObject",
              "s3:DeleteObject"
            ],
            "Resource": [Join("", ["arn:aws:s3:::", Ref('S3Bucket'), '/Backup/*'])]
        },
        {
            "Sid": "Stmt1500612724002",
            "Effect": "Allow",
            "Action": [
              "kms:Encrypt",
              "kms:Decrypt",
              "kms:GenerateDataKey*"
            ],
            "Resource": [ OpenEMRKeyARN ]
        }
    ]

    if (args.recovery):
        rolePolicyStatements.extend([
            {
                "Sid": "Stmt1500699052004",
                "Effect": "Allow",
                "Action": ["s3:ListBucket"],
                "Resource" : [Join("", ["arn:aws:s3:::", Ref('RecoveryS3Bucket')])]
            },
            {
                "Sid": "Stmt1500699052005",
                "Effect": "Allow",
                "Action": [
                  "s3:GetObject",
                ],
                "Resource": [Join("", ["arn:aws:s3:::", Ref('RecoveryS3Bucket'), '/Backup/*'])]
            },
        ])

    t.add_resource(
        iam.ManagedPolicy(
            'NFSBackupPolicy',
            Description='Policy for ongoing NFS backup instance',
            PolicyDocument = {
                "Version": "2012-10-17",
                "Statement": rolePolicyStatements
            }
        )
    )

    t.add_resource(
        iam.Role(
            'NFSBackupRole',
            AssumeRolePolicyDocument = {
               "Version" : "2012-10-17",
               "Statement": [ {
                  "Effect": "Allow",
                  "Principal": {
                     "Service": [ "ec2.amazonaws.com" ]
                  },
                  "Action": [ "sts:AssumeRole" ]
               } ]
            },
            Path='/',
            ManagedPolicyArns= [Ref('NFSBackupPolicy')]
        )
    )

    t.add_resource(
        iam.InstanceProfile(
            'NFSInstanceProfile',
            Path = '/',
            Roles = [Ref('NFSBackupRole')]
        )
    )

    bootstrapScript = [
        "#!/bin/bash -x\n",
        "exec > /tmp/part-001.log 2>&1\n",
        "apt-get -y update\n",
        "apt-get -y install python-pip\n",
        "pip install https://s3.amazonaws.com/cloudformation-examples/aws-cfn-bootstrap-latest.tar.gz\n",
        "cfn-init -v ",
        "         --stack ", ref_stack_name,
        "         --resource NFSBackupInstance ",
        "         --configsets Setup ",
        "         --region ", ref_region, "\n",
        "cfn-signal -e $? ",
        "         --stack ", ref_stack_name,
        "         --resource NFSBackupInstance ",
        "         --region ", ref_region, "\n"
    ]

    setupScript = [
        "#!/bin/bash\n",
        "S3=", Ref('S3Bucket'), "\n",
        "KMS=", OpenEMRKeyID, "\n",
        "apt-get -y update\n",
        "DEBIAN_FRONTEND=noninteractive apt-get dist-upgrade -y -o Dpkg::Options::=\"--force-confdef\" -o Dpkg::Options::=\"--force-confold\" --force-yes\n",
        "apt-get -y install duplicity python-boto nfs-common awscli\n",
        "mkdir /mnt/efs\n",
        "echo \"nfs.openemr.local:/ /mnt/efs nfs4 nfsvers=4.1,rsize=1048576,wsize=1048576,hard,timeo=600,retrans=2 0 0\" >> /etc/fstab\n",
        "mount /mnt/efs\n",
        "touch /tmp/mypass\n",
        "chmod 500 /tmp/mypass\n",
        "openssl rand -base64 32 >> /tmp/mypass\n",
        "aws s3 cp /tmp/mypass s3://$S3/Backup/passphrase.txt --sse aws:kms --sse-kms-key-id $KMS\n",
        "rm /tmp/mypass\n"
    ]

    backupScript = [
        "#!/bin/bash\n",
        "S3=", Ref('S3Bucket'), "\n",
        "KMS=", OpenEMRKeyID, "\n",
        "PASSPHRASE=`aws s3 cp s3://$S3/Backup/passphrase.txt - --sse aws:kms --sse-kms-key-id $KMS`\n",
        "export PASSPHRASE\n",
        "duplicity --full-if-older-than 1M /mnt/efs s3://s3.amazonaws.com/$S3/Backup\n",
        "duplicity remove-all-but-n-full 2 --force s3://s3.amazonaws.com/$S3/Backup\n"
    ]

    generalRecoveryScript = [
        "#!/bin/bash\n",
        "S3=", Ref('S3Bucket'), "\n",
        "KMS=", OpenEMRKeyID, "\n",
        "PASSPHRASE=`aws s3 cp s3://$S3/Backup/passphrase.txt - --sse aws:kms --sse-kms-key-id $KMS`\n",
        "export PASSPHRASE\n",
        "duplicity --force s3://s3.amazonaws.com/$S3/Backup /mnt/efs\n"
    ]

    bootstrapInstall = cloudformation.InitConfig(
        files = {
            "/root/setup.sh" : {
                "content" : Join("", setupScript),
                "mode"  : "000500",
                "owner" : "root",
                "group" : "root"
            },
            "/etc/cron.daily/backup.sh" : {
                "content" : Join("", backupScript),
                "mode"  : "000500",
                "owner" : "root",
                "group" : "root"
            },
            "/root/recovery.sh" : {
                "content" : Join("", generalRecoveryScript),
                "mode"  : "000500",
                "owner" : "root",
                "group" : "root"
            }
        },
        commands = {
            "01_setup" : {
              "command" : "/root/setup.sh"
            }
        }
    )

    if (args.recovery):

        stackRecoveryScript = [
            "#!/bin/bash\n",
            "S3=", Ref('RecoveryS3Bucket'), "\n",
            "KMS=", OpenEMRKeyID, "\n",
            "PASSPHRASE=`aws s3 cp s3://$S3/Backup/passphrase.txt - --sse aws:kms --sse-kms-key-id $KMS`\n",
            "export PASSPHRASE\n",
            "duplicity --force s3://s3.amazonaws.com/$S3/Backup /mnt/efs\n"
        ]

        bootstrapRecovery = cloudformation.InitConfig(
            files = {
                "/root/stackRestore.sh" : {
                    "content" : Join("", stackRecoveryScript),
                    "mode"  : "000500",
                    "owner" : "root",
                    "group" : "root"
                }
            },
            commands = {
                "02_recover" : {
                  "command" : "/root/stackRestore.sh"
                }
            }
        )

        bootstrapMetadata = cloudformation.Metadata(
            cloudformation.Init(
                cloudformation.InitConfigSets(
                    Setup = ['Install','Recover']
                ),
                Install=bootstrapInstall,
                Recover=bootstrapRecovery
            )
        )
    else:
        bootstrapMetadata = cloudformation.Metadata(
            cloudformation.Init(
                cloudformation.InitConfigSets(
                    Setup = ['Install']
                ),
                Install=bootstrapInstall
            )
        )

    t.add_resource(
        ec2.Instance(
            'NFSBackupInstance',
            DependsOn = ['rtPrivate2Attach', 'DNSEFS'],
            Metadata = bootstrapMetadata,
            ImageId = FindInMap('RegionData', ref_region, 'UbuntuAMI'),
            InstanceType = 't2.nano',
            SubnetId = Ref('PrivateSubnet2'),
            KeyName = Ref('EC2KeyPair'),
            SecurityGroupIds = [Ref('NFSBackupSecurityGroup')],
            IamInstanceProfile = Ref('NFSInstanceProfile'),
            Tags = Tags(Name='NFS Backup Agent'),
            InstanceInitiatedShutdownBehavior = 'stop',
            UserData = Base64(Join('', bootstrapScript)),
            CreationPolicy = {
              "ResourceSignal" : {
                "Timeout" : "PT15M" if args.recovery else "PT5M"
              }
            }
        )
    )

    t.add_resource(
        route53.RecordSetType(
            'DNSBackupAgent',
            HostedZoneId = Ref('DNS'),
            Name = 'nfsbackups.openemr.local',
            Type = 'CNAME',
            TTL = '900',
            ResourceRecords = [GetAtt('NFSBackupInstance', 'PrivateDnsName')]
        )
    )

    return t

def buildDocumentStore(t, args):
    t.add_resource(
        ec2.SecurityGroup(
            'CouchDBSecurityGroup',
            GroupDescription = 'Patient Document Access',
            VpcId = Ref('VPC'),
            Tags = Tags(Name='Patient Documents')
        )
    )

    t.add_resource(
        ec2.SecurityGroupIngress(
            'CouchDBSGIngress',
            GroupId = Ref('CouchDBSecurityGroup'),
            IpProtocol = '-1',
            SourceSecurityGroupId = Ref('ApplicationSecurityGroup')
        )
    )

    if (args.recovery):
        t.add_resource(
            ec2.Volume(
                'CouchDBVolume',
                DeletionPolicy = 'Delete' if args.dev else 'Snapshot',
                AvailabilityZone = Select("0", GetAZs("")),
                VolumeType = 'sc1',
                SnapshotId = Ref('RecoveryCouchDBSnapshot'),
                Tags=Tags(Name="Patient Documents")
            )
        )
    else:
        t.add_resource(
            ec2.Volume(
                'CouchDBVolume',
                DeletionPolicy = 'Delete' if args.dev else 'Snapshot',
                Size=Ref('DocumentStorage'),
                AvailabilityZone = Select("0", GetAZs("")),
                VolumeType = 'sc1',
                Encrypted = True,
                KmsKeyId = OpenEMRKeyID,
                Tags=Tags(Name="Patient Documents")
            )
        )

    t.add_resource(
        iam.ManagedPolicy(
            'CouchDBPolicy',
            Description='Policy to retrieve CouchDB SSL credentials',
            PolicyDocument = {
                "Version": "2012-10-17",
                "Statement": [
                    {
                      "Sid": "Stmt1500699052000",
                      "Effect": "Allow",
                      "Action": [
                          "s3:GetObject"
                      ],
                      "Resource": [
                          { "Fn::Join" : ["", ["arn:aws:s3:::", Ref('S3Bucket'), "/CA/certs/*"]]},
                          { "Fn::Join" : ["", ["arn:aws:s3:::", Ref('S3Bucket'), "/CA/keys/couch.key"]]}
                      ]
                    },
                    {
                      "Sid": "Stmt1500612724002",
                      "Effect": "Allow",
                      "Action": [
                          "kms:Decrypt"
                      ],
                      "Resource": [ OpenEMRKeyARN ]
                    }
                ]
            }
        )
    )

    t.add_resource(
        iam.Role(
            'CouchDBRole',
            AssumeRolePolicyDocument = {
               "Version" : "2012-10-17",
               "Statement": [ {
                  "Effect": "Allow",
                  "Principal": {
                     "Service": [ "ec2.amazonaws.com" ]
                  },
                  "Action": [ "sts:AssumeRole" ]
               } ]
            },
            Path='/',
            ManagedPolicyArns= [Ref('CouchDBPolicy')]
        )
    )

    t.add_resource(
        iam.InstanceProfile(
            'CouchDBInstanceProfile',
            Path = '/',
            Roles = [Ref('CouchDBRole')]
        )
    )

    bootstrapScript = [
        "#!/bin/bash -x\n",
        "exec > /tmp/part-001.log 2>&1\n",
        "apt-get -y update\n",
        "apt-get -y install python-pip\n",
        "pip install https://s3.amazonaws.com/cloudformation-examples/aws-cfn-bootstrap-latest.tar.gz\n",
        "cfn-init -v ",
        "         --stack ", ref_stack_name,
        "         --resource CouchDBInstance ",
        "         --configsets Setup ",
        "         --region ", ref_region, "\n",
        "cfn-signal -e $? ",
        "         --stack ", ref_stack_name,
        "         --resource CouchDBInstance ",
        "         --region ", ref_region, "\n"
    ]

    ipIniFile = [
        "[httpd]\n",
        "bind_address = 0.0.0.0\n"
    ]

    sslIniFile = [
        "[daemons]\n",
        "httpsd = {couch_httpd, start_link, [https]}\n",
        "[ssl]\n",
        "port = 6984\n",
        "key_file = /etc/couchdb/couch.key\n",
        "cert_file = /etc/couchdb/couch.crt\n",
        "cacert_file = /etc/couchdb/ca.crt\n"
    ]

    replicatorIniFile = [
        "[replicator]\n",
        "ssl_trusted_certificates_file = /etc/couchdb/ca.crt\n",
        "verify_ssl_certificates = true\n"
    ]

    fstabFile = [
        "/dev/xvdd /mnt/db ext4 defaults,nofail 0 0\n"
    ]

    if (args.recovery):
        setupScript = [
            "#!/bin/bash -xe\n",
            "exec > /tmp/part-002.log 2>&1\n",
            "DEBIAN_FRONTEND=noninteractive apt-get dist-upgrade -y -o Dpkg::Options::=\"--force-confdef\" -o Dpkg::Options::=\"--force-confold\" --force-yes\n",
            "mkdir /mnt/db\n",
            "cat /root/fstab.append >> /etc/fstab\n",
            "mount /mnt/db\n",
            "apt-get -y install couchdb awscli\n",
            "service couchdb stop\n",
            "aws configure set s3.signature_version s3v4\n",
            "aws s3 cp s3://", Ref('S3Bucket'), "/CA/certs/ca.crt /etc/couchdb\n",
            "aws s3 cp s3://", Ref('S3Bucket'), "/CA/certs/couch.crt /etc/couchdb\n",
            "chmod 664 /etc/couchdb/*.crt\n",
            "aws s3 cp s3://", Ref('S3Bucket'), "/CA/keys/couch.key /etc/couchdb --sse aws:kms --sse-kms-key-id ", OpenEMRKeyID, "\n",
            "chmod 660 /etc/couchdb/couch.key\n",
            "chown couchdb:couchdb /etc/couchdb/*.crt /etc/couchdb/*.key\n",
            "rm -rf /var/lib/couchdb\n",
            "ln -s /mnt/db/couchdb /var/lib/couchdb\n",
            "cp /root/ip.ini /root/ssl.ini /root/replicator.ini /etc/couchdb/local.d\n",
            "chown couchdb:couchdb /etc/couchdb/local.d/ip.ini /etc/couchdb/local.d/replicator.ini /etc/couchdb/local.d/ssl.ini\n",
            "service couchdb start\n"
        ]
    else:
        setupScript = [
            "#!/bin/bash -xe\n",
            "exec > /tmp/part-002.log 2>&1\n",
            "DEBIAN_FRONTEND=noninteractive apt-get dist-upgrade -y -o Dpkg::Options::=\"--force-confdef\" -o Dpkg::Options::=\"--force-confold\" --force-yes\n",
            "mkfs -t ext4 /dev/xvdd\n",
            "mkdir /mnt/db\n",
            "cat /root/fstab.append >> /etc/fstab\n",
            "mount /mnt/db\n",
            "apt-get -y install couchdb awscli\n",
            "service couchdb stop\n",
            "aws configure set s3.signature_version s3v4\n",
            "aws s3 cp s3://", Ref('S3Bucket'), "/CA/certs/ca.crt /etc/couchdb\n",
            "aws s3 cp s3://", Ref('S3Bucket'), "/CA/certs/couch.crt /etc/couchdb\n",
            "chmod 664 /etc/couchdb/*.crt\n",
            "aws s3 cp s3://", Ref('S3Bucket'), "/CA/keys/couch.key /etc/couchdb --sse aws:kms --sse-kms-key-id ", OpenEMRKeyID, "\n",
            "chmod 660 /etc/couchdb/couch.key\n",
            "chown couchdb:couchdb /etc/couchdb/*.crt /etc/couchdb/*.key\n",
            "mv /var/lib/couchdb /mnt/db/couchdb\n",
            "ln -s /mnt/db/couchdb /var/lib/couchdb\n",
            "cp /root/ip.ini /root/ssl.ini /root/replicator.ini /etc/couchdb/local.d\n",
            "chown couchdb:couchdb /etc/couchdb/local.d/ip.ini /etc/couchdb/local.d/replicator.ini /etc/couchdb/local.d/ssl.ini\n",
            "service couchdb start\n"
            "sleep 5\n"
            "curl -k -X PUT https://127.0.0.1:6984/couchdb\n"
        ]

    bootstrapInstall = cloudformation.InitConfig(
        files = {
            "/root/couchdb.setup.sh" : {
                "content" : Join("", setupScript),
                "mode"  : "000500",
                "owner" : "root",
                "group" : "root"
            },
            "/root/ip.ini" : {
                "content" : Join("", ipIniFile),
                "mode"  : "000400",
                "owner" : "root",
                "group" : "root"
            },
            "/root/ssl.ini" : {
                "content" : Join("", sslIniFile),
                "mode"  : "000400",
                "owner" : "root",
                "group" : "root"
            },
            "/root/replicator.ini" : {
                "content" : Join("", replicatorIniFile),
                "mode"  : "000400",
                "owner" : "root",
                "group" : "root"
            },
            "/root/fstab.append" : {
                "content" : Join("", fstabFile),
                "mode"  : "000400",
                "owner" : "root",
                "group" : "root"
            }
        },
        commands = {
            "01_setup" : {
              "command" : "/root/couchdb.setup.sh"
            }
        }
    )

    # this is incomplete -- design documents will not replicate between systems, since ''"user_ctx" = {"roles": ["_admin"]}' is not specified on local targets.
    # Is this acceptable or is this broken? I don't think there's a document /search/ feature...
    # Fix will involve either:
    # * A: moving the replicate script to both servers to properly connect user_ctx to local target
    # * B: configuring and employing admin user for replication
    replicateScript = [
        "#!/bin/bash -xe\n",
        "exec > /tmp/part-003.log 2>&1\n",
        'curl -k -X POST https://127.0.0.1:6984/_replicator -d \'{"source":"https://couchdb-az1.openemr.local:6984/couchdb", "target":"couchdb", "continuous":true}\' -H "Content-Type: application/json"\n',
        'curl -k -X POST https://127.0.0.1:6984/_replicator -d \'{"source":"couchdb", "target":"https://couchdb-az1.openemr.local:6984/couchdb", "continuous":true}\' -H "Content-Type: application/json"\n'
    ]

    bootstrapReplicate = cloudformation.InitConfig(
        files = {
            "/root/couchdb.replicate.sh" : {
                "content" : Join("", replicateScript),
                "mode"  : "000500",
                "owner" : "root",
                "group" : "root"
            }
        },
        commands = {
            "01_setup" : {
              "command" : "/root/couchdb.replicate.sh"
            }
        }
    )

    bootstrapMetadata = cloudformation.Metadata(
        cloudformation.Init(
            cloudformation.InitConfigSets(
                Setup = ['Install']
            ),
            Install=bootstrapInstall
        )
    )

    # it honestly should take <5, but I had it take almost 20 once in testing
    t.add_resource(
        ec2.Instance(
            'CouchDBInstance',
            DependsOn = ['CertWriterInstance'],
            Metadata = bootstrapMetadata,
            ImageId = FindInMap('RegionData', ref_region, 'UbuntuAMI'),
            InstanceType = 't2.micro',
            SubnetId = Ref('PrivateSubnet1'),
            KeyName = Ref('EC2KeyPair'),
            SecurityGroupIds = [Ref('CouchDBSecurityGroup')],
            IamInstanceProfile = Ref('CouchDBInstanceProfile'),
            Volumes = [{
                "Device" : "/dev/sdd",
                "VolumeId" : Ref('CouchDBVolume')
            }],
            Tags = Tags(Name='Patient Document Store'),
            InstanceInitiatedShutdownBehavior = 'stop',
            UserData = Base64(Join('', bootstrapScript)),
            CreationPolicy = {
              "ResourceSignal" : {
                "Timeout" : "PT25M"
              }
            }
        )
    )


    if (args.dual_az):
        t.add_resource(
            ec2.SecurityGroupIngress(
                'CouchDBSGIngress2',
                GroupId = Ref('CouchDBSecurityGroup'),
                IpProtocol = '-1',
                SourceSecurityGroupId = Ref('CouchDBSecurityGroup')
            )
        )

        if (args.recovery):
            t.add_resource(
                ec2.Volume(
                    'RCouchDBVolume',
                    DeletionPolicy = 'Delete',
                    AvailabilityZone = Select("1", GetAZs("")),
                    VolumeType = 'sc1',
                    SnapshotId = Ref('RecoveryCouchDBSnapshot'),
                    Tags=Tags(Name="Patient Documents")
                )
            )
        else:
            t.add_resource(
                ec2.Volume(
                    'RCouchDBVolume',
                    DeletionPolicy = 'Delete',
                    Size=Ref('DocumentStorage'),
                    AvailabilityZone = Select("1", GetAZs("")),
                    VolumeType = 'sc1',
                    Encrypted = True,
                    KmsKeyId = OpenEMRKeyID,
                    Tags=Tags(Name="Patient Documents")
                )
            )

        bootstrapReplicatedMetadata = cloudformation.Metadata(
            cloudformation.Init(
                cloudformation.InitConfigSets(
                    Setup = ['Install', 'StartReplication']
                ),
                Install=bootstrapInstall,
                StartReplication=bootstrapReplicate
            )
        )

        bootstrapReplicatorScript = [
            "#!/bin/bash -x\n",
            "exec > /tmp/part-001.log 2>&1\n",
            "apt-get -y update\n",
            "apt-get -y install python-pip\n",
            "pip install https://s3.amazonaws.com/cloudformation-examples/aws-cfn-bootstrap-latest.tar.gz\n",
            "cfn-init -v ",
            "         --stack ", ref_stack_name,
            "         --resource CouchReplicatedDBInstance ",
            "         --configsets Setup ",
            "         --region ", ref_region, "\n",
            "cfn-signal -e $? ",
            "         --stack ", ref_stack_name,
            "         --resource CouchReplicatedDBInstance ",
            "         --region ", ref_region, "\n"
        ]

        t.add_resource(
            ec2.Instance(
                'CouchReplicatedDBInstance',
                DependsOn = ['CertWriterInstance', 'CouchDBInstance'],
                Metadata = bootstrapReplicatedMetadata,
                ImageId = FindInMap('RegionData', ref_region, 'UbuntuAMI'),
                InstanceType = 't2.micro',
                SubnetId = Ref('PrivateSubnet2'),
                KeyName = Ref('EC2KeyPair'),
                SecurityGroupIds = [Ref('CouchDBSecurityGroup')],
                IamInstanceProfile = Ref('CouchDBInstanceProfile'),
                Volumes = [{
                    "Device" : "/dev/sdd",
                    "VolumeId" : Ref('RCouchDBVolume')
                }],
                Tags = Tags(Name='Patient Document Store'),
                InstanceInitiatedShutdownBehavior = 'stop',
                UserData = Base64(Join('', bootstrapReplicatorScript)),
                CreationPolicy = {
                  "ResourceSignal" : {
                    "Timeout" : "PT25M"
                  }
                }
            )
        )

        t.add_resource(
            route53.RecordSetType(
                'DNSCouchDBAZ1',
                HostedZoneId = Ref('DNS'),
                Name = 'couchdb-az1.openemr.local',
                Type = 'CNAME',
                TTL = '900',
                ResourceRecords = [GetAtt('CouchDBInstance', 'PrivateDnsName')]
            )
        )

        t.add_resource(
            route53.RecordSetType(
                'DNSCouchDBAZ2',
                HostedZoneId = Ref('DNS'),
                Name = 'couchdb-az2.openemr.local',
                Type = 'CNAME',
                TTL = '900',
                ResourceRecords = [GetAtt('CouchReplicatedDBInstance', 'PrivateDnsName')]
            )
        )

    t.add_resource(
        route53.RecordSetType(
            'DNSCouchDB',
            HostedZoneId = Ref('DNS'),
            Name = 'couchdb.openemr.local',
            Type = 'CNAME',
            TTL = '900',
            ResourceRecords = [GetAtt('CouchDBInstance', 'PrivateDnsName')]
        )
    )

    return t

def buildDocumentBackups(t):
    t.add_resource(
        iam.Role(
            'DocumentBackupExecutionRole',
            AssumeRolePolicyDocument = {
               "Version" : "2012-10-17",
               "Statement": [ {
                  "Effect": "Allow",
                  "Principal": {
                     "Service": [ "lambda.amazonaws.com" ]
                  },
                  "Action": [ "sts:AssumeRole" ]
               } ]
            },
            Path='/',
            Policies=[iam.Policy(
                PolicyName="root",
                PolicyDocument= {
                    "Version": "2012-10-17",
                    "Statement": [
                      { "Effect": "Allow", "Action": ["logs:*"], "Resource": "arn:aws:logs:*:*:*" },
                      {
                          "Effect": "Allow",
                          "Action": [
                              "ec2:DescribeVolumeStatus",
                              "ec2:DescribeSnapshots",
                              "ec2:CreateSnapshot",
                              "ec2:DeleteSnapshot"
                          ],
                          "Resource": [
                              "*"
                          ]
                      }
                    ]
                }
            )]
        )
    )

    lambdaScript = [
        "import boto3",
        "import os",
        "def lambda_handler(event, context):",
        "  volume = boto3.session.Session(region_name = os.environ['AWS_DEFAULT_REGION']).resource('ec2').Volume(os.environ['VOLUME_ID'])",
        "  volume.create_snapshot(os.environ['DESCRIPTION'])",
        "  snapshots = sorted(volume.snapshots.all(), key=lambda x: x.start_time)",
        "  if len(snapshots) > os.environ['COUNTRETAINED']:",
        "    for i in range(0,len(snapshots)-os.environ['COUNTRETAINED']):",
        "      snapshots[i].delete()",
        "  return 'all OK'"
    ]

    t.add_resource(
        awslambda.Function(
            'DocumentBackupManagerFunction',
            Description='handles patient document (CouchDB) backups',
            Handler='index.lambda_handler',
            Role=GetAtt('DocumentBackupExecutionRole','Arn'),
            Code=awslambda.Code(
                ZipFile=Join('\n', lambdaScript)
            ),
            Environment=awslambda.Environment(
                Variables={
                    "VOLUME_ID" : Ref("CouchDBVolume"),
                    "DESCRIPTION" : "OpenEMR document backup",
                    "COUNTRETAINED" : 3
                }
            ),
            Runtime="python2.7",
            Timeout="15"
        )
    )

    t.add_resource(
        events.Rule(
            'DocumentBackupScheduler',
            Description='BackupRule',
            ScheduleExpression='rate(1 day)',
            State='ENABLED',
            Targets=[events.Target(
                Arn=GetAtt('DocumentBackupManagerFunction', 'Arn'),
                Id='BackupManagerV1'
            )]
        )
    )

    t.add_resource(
        awslambda.Permission(
            'DocumentBackupSchedulerPermission',
            FunctionName=Ref('DocumentBackupManagerFunction'),
            Action='lambda:InvokeFunction',
            Principal='events.amazonaws.com',
            SourceArn=GetAtt('DocumentBackupScheduler', 'Arn')
        )
    )
    return t

def buildApplication(t, args):

    t.add_resource(
        iam.Role(
            'BeanstalkInstanceRole',
            AssumeRolePolicyDocument = {
               "Version" : "2012-10-17",
               "Statement": [ {
                  "Effect": "Allow",
                  "Principal": {
                     "Service": [ "ec2.amazonaws.com" ]
                  },
                  "Action": [ "sts:AssumeRole" ]
               } ]
            },
            Path='/',
            Policies=[iam.Policy(
                PolicyName="root",
                PolicyDocument= {
                    "Version": "2012-10-17",
                    "Statement": [
                        {
                            "Sid": "BucketAccess",
                            "Action": [
                            "s3:Get*",
                            "s3:List*",
                            "s3:PutObject"
                            ],
                            "Effect": "Allow",
                            "Resource": [
                            "arn:aws:s3:::elasticbeanstalk-*",
                            "arn:aws:s3:::elasticbeanstalk-*/*"
                            ]
                        },
                        {
                            "Sid": "XRayAccess",
                            "Action":[
                                "xray:PutTraceSegments",
                                "xray:PutTelemetryRecords"
                            ],
                            "Effect": "Allow",
                            "Resource": "*"
                        },
                        {
                            "Sid": "CloudWatchLogsAccess",
                            "Action": [
                                "logs:PutLogEvents",
                                "logs:CreateLogStream"
                            ],
                            "Effect": "Allow",
                            "Resource": [ "arn:aws:logs:*:*:log-group:/aws/elasticbeanstalk*" ]
                        },
                        {
                            "Sid": "Stmt1500699052000",
                            "Effect": "Allow",
                            "Action": [
                              "s3:GetObject"
                            ],
                            "Resource": [
                                Join("", ["arn:aws:s3:::", Ref('S3Bucket'), "/CA/certs/*"]),
                                Join("", ["arn:aws:s3:::", Ref('S3Bucket'), "/CA/keys/beanstalk.key"])
                            ]
                        },
                        {
                          "Sid": "Stmt1500612724002",
                          "Effect": "Allow",
                          "Action": [
                              "kms:Decrypt"
                          ],
                          "Resource": [ OpenEMRKeyARN ]
                        }
                    ]
                }
            )]
        )
    )

    t.add_resource(
        iam.InstanceProfile(
            'BeanstalkInstanceProfile',
            Path = '/',
            Roles = [Ref('BeanstalkInstanceRole')]
        )
    )

    t.add_resource(elasticbeanstalk.Application(
        "EBApplication",
        Description="OpenEMR Application Stack"
    ))

    t.add_resource(elasticbeanstalk.ApplicationVersion(
        "EBApplicationVersion",
        Description="Version 1.0",
        ApplicationName=Ref("EBApplication"),
        SourceBundle=elasticbeanstalk.SourceBundle(
            S3Bucket=FindInMap("RegionData", ref_region, "RegionBucket"),
            S3Key=FindInMap("RegionData", ref_region, "ApplicationSource")
        )
    ))

    options = [
        elasticbeanstalk.OptionSettings(
            Namespace='aws:autoscaling:launchconfiguration',
            OptionName='SecurityGroups',
            Value=Ref('ApplicationSecurityGroup')
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:autoscaling:launchconfiguration',
            OptionName='EC2KeyName',
            Value=Ref('EC2KeyPair')
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:autoscaling:launchconfiguration',
            OptionName='IamInstanceProfile',
            Value=GetAtt('BeanstalkInstanceProfile', 'Arn')
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:autoscaling:launchconfiguration',
            OptionName='InstanceType',
            Value='t2.micro'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elb:listener',
            OptionName='InstanceProtocol',
            Value='HTTPS'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elb:listener',
            OptionName='InstancePort',
            Value='443'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elb:policies',
            OptionName='ConnectionDrainingEnabled',
            Value="true"
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elb:policies',
            OptionName='ConnectionSettingIdleTimeout',
            Value='3600'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elb:policies',
            OptionName='Stickiness Policy',
            Value='true'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elb:policies:backendencryption',
            OptionName='PublicKeyPolicyNames',
            Value='backendkey'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elb:policies:backendencryption',
            OptionName='InstancePorts',
            Value='443'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elb:policies:backendkey',
            OptionName='PublicKey',
            Value=GetAtt('EBCert','PublicKey')
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:ec2:vpc',
            OptionName='VPCId',
            Value=Ref('VPC')
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:ec2:vpc',
            OptionName='Subnets',
            Value=Join(',', [Ref('PrivateSubnet1'), Ref('PrivateSubnet2')])
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:ec2:vpc',
            OptionName='ELBSubnets',
            Value=Join(',', [Ref('PublicSubnet1'), Ref('PublicSubnet2')])
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elasticbeanstalk:application',
            OptionName='Application Healthcheck URL',
            Value='HTTPS:443/openemr/version.php'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elasticbeanstalk:application:environment',
            OptionName='REDIS_IP',
            Value='redis.openemr.local'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elasticbeanstalk:application:environment',
            OptionName='FILE_SYSTEM_ID',
            Value=Ref('ElasticFileSystem')
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elasticbeanstalk:application:environment',
            OptionName='NFS_HOSTNAME',
            Value='nfs.openemr.local'
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elasticbeanstalk:application:environment',
            OptionName='S3BUCKET',
            Value=Ref('S3Bucket')
        ),
        elasticbeanstalk.OptionSettings(
            Namespace='aws:elasticbeanstalk:application:environment',
            OptionName='KMSKEY',
            Value=OpenEMRKeyID
        )
    ]

    if (args.dual_az):
        couchDBZoneFile = [
            '{ "segment24": {',
            '"10.0.1": "couchdb-az1.openemr.local",',
            '"10.0.2": "couchdb-az1.openemr.local",',
            '"10.0.3": "couchdb-az2.openemr.local",',
            '"10.0.4": "couchdb-az2.openemr.local",',
            '} }'
        ]
        options.extend([
            elasticbeanstalk.OptionSettings(
                Namespace='aws:elasticbeanstalk:application:environment',
                OptionName='COUCHDBZONE',
                Value=Join("", couchDBZoneFile)
            ), elasticbeanstalk.OptionSettings(
                Namespace='aws:autoscaling:asg',
                OptionName='MinSize',
                Value='2'
            )
        ])

    if (not args.recovery):
        options.append(elasticbeanstalk.OptionSettings(
            Namespace='aws:elasticbeanstalk:application:environment',
            OptionName='TIMEZONE',
            Value=Ref('TimeZone')
        ))

    t.add_resource(
        elasticbeanstalk.Environment(
            'EBEnvironment',
            DependsOn = ["DNSEFS", "DNSRedis", "DNSCouchDB", "DNSMySQL"],
            ApplicationName = Ref('EBApplication'),
            Description = 'OpenEMR v5.0.0 cloud deployment',
            SolutionStackName = '64bit Amazon Linux 2017.03 v2.4.3 running PHP 7.0',
            VersionLabel = Ref('EBApplicationVersion'),
            OptionSettings = options
        )
    )

    return t

def setOutputs(t, args):
    t.add_output(
        Output(
            'OpenEMR',
            Description='OpenEMR Recovery' if args.recovery else 'OpenEMR Setup',
            Value=Join('', ['http://', GetAtt('EBEnvironment', 'EndpointURL'), '/openemr'])
        )
    )
    return t

parser = argparse.ArgumentParser(description="OpenEMR stack builder")
parser.add_argument("--beanstalk-key", help="select compressed OpenEMR beanstalk", default=currentBeanstalkKey)
parser.add_argument("--dual-az", help="build AZ-hardened stack [in progress!]", action="store_true")
parser.add_argument("--recovery", help="load OpenEMR stack from backups", action="store_true")
parser.add_argument("--dev", help="build [security breaching!] development resources", action="store_true")
parser.add_argument("--force-bastion", help="force developer bastion outside of development", action="store_true")
args = parser.parse_args()

t = Template()

t.add_version('2010-09-09')
descString='OpenEMR v5.0.0.4 cloud deployment'
if (args.dev):
    descString+=' [developer]'
if (args.force_bastion):
    descString+=' [keyhole]'
if (args.dual_az):
    descString+=' [dual-AZ]'
if (args.recovery):
    descString+=' [recovery]'
if (not args.beanstalk_key == currentBeanstalkKey):
    descString+=' [eb: ' + args.beanstalk_key + ']'
t.add_description(descString)

# reduce to consistent names
if (args.recovery):
    OpenEMRKeyID = Select('1', Split('/', Ref('RecoveryKMSKey')))
    OpenEMRKeyARN = Ref('RecoveryKMSKey')
else:
    OpenEMRKeyID = Ref('OpenEMRKey')
    OpenEMRKeyARN = GetAtt('OpenEMRKey', 'Arn')

setInputs(t,args)
setMappings(t,args)
buildVPC(t, args.dual_az)
buildFoundation(t, args)
if (args.dev or args.force_bastion):
    buildDeveloperBastion(t)
buildEFS(t, args.dev)
buildRedis(t, args.dual_az)
buildMySQL(t, args)
buildCertWriter(t, args.dev)
buildNFSBackup(t, args)
buildDocumentStore(t, args)
buildDocumentBackups(t)
buildApplication(t, args)
setOutputs(t, args)

print(t.to_json())
