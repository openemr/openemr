<?php

/*
 *  $Id: 2e1e3730b96f5d5ea32d54fda8d9333b34eeaaba $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

/**
 * Produce license files using Zeng Guard.
 * The task can produce a license file from the given
 * license properties or it can use a template.
 *
 * @author    Petr Rybak <petr@rynawe.net>
 * @version   $Id: 2e1e3730b96f5d5ea32d54fda8d9333b34eeaaba $
 * @package   phing.tasks.ext.zendguard
 * @since     2.4.3
 */
class ZendGuardLicenseTask extends Task
{
    protected $zendsignCommand;
    private $tmpLicensePath;

    /**
     * TASK PROPERTIES
     *
     * See http://static.zend.com/topics/Zend-Guard-User-Guidev5x.pdf
     * for more information on how to use ZendGuard
     *
     */
    /**
     * Path to Zend Guard zendenc_sign executable
     *
     * @var string
     */
    protected $zendsignPath;
    /**
     * Path to private key that will be used to sign the license
     *
     * @var string
     */
    protected $privateKeyPath;
    /**
     * Where to store the signed license file
     *
     * @var string
     */
    protected $outputFile;
    /**
     * Path to license template. If specified all
     * license properties will be ignored and the
     * template will be used to generate the file.
     *
     * @var string
     */
    protected $licenseTemplate;
    /**
     * The name assigned to Product. This must be the same name used when encoding
     * the PHP files.
     *
     * REQUIRED
     *
     * @var string
     */
    protected $productName;
    /**
     * The Name of the Registered owner of the license.
     *
     * REQUIRED
     *
     * @var string
     */
    protected $registeredTo;
    /**
     * Expiration date of the license. Used if the license is issued with a date restriction.
     * Possible values:
     *     - 'Never', '0' or false: the license won't expire
     *     - A Date in format DD-MM-YYYY to set expiration for that date
     *     - Relative date supported by the PHP strtotime function (e.g. +1 month)
     *
     * REQUIRED
     *
     * @var string
     */
    protected $expires;
    /**
     * Limits the use of the license to IP addresses that fall within specification. Supports
     * wildcards for any of the IP place holders, as well as the two types of net masks
     * (filters).
     * Netmask pair An IP a.b.c.d, and a netmask w.x.y.z. (That is., 10.1.0.0/255.255.0.0),
     * where the binary of mask is applied to filter IP addresses.
     * ip/nnn (similar to a CIDR specification) This mask consists of nnn high-order 1 bits.
     * (That is, 10.1.0.0/16 is the same as 10.1.0.0/255.255.0.0). Instead of spelling out
     * the bits of the subnet mask, this mask notation is simply listed as the number of 1s
     * bits that start the mask. Rather than writing the address and subnet mask as
     * 192.60.128.0/255.255.252.0 the network address would be written simply as:
     * 192.60.128.0/22 which indicates starting address of the network and number of 1s
     * bits (22) in the network portion of the address. The mask in binary is
     * (11111111.11111111.11111100.00000000).
     *
     * OPTIONAL
     *
     * Example (Wildcard):
     * IP-Range = 10.1.*.*
     * Example (Net Mask):
     * IP-Range = 10.1.0.0/255.255.0.0
     * Example (Net Mask):
     * IP-Range = 10.1.0.0/16
     *
     * @var string
     */
    protected $ipRange;
    /**
     * Coded string (Zend Host ID) used to lock the license to a specific hardware. The
     * Zend Host ID obtained from the machine where the encoded files and license are
     * to be installed. The Zend Host ID code can be obtained by using the zendid utility.
     * For more details, see Getting the Zend Host ID.
     *
     * REQUIRED if Hardware-Locked is set equal to YES.
     * Meaningless if Hardware-Locked is set equal to NO.
     *
     * User semicolon to enter more than one Host-ID
     *
     * Example:
     * Host-ID = H:MFM43-Q9CXC-B9EDX-GWYSU;H:MFM43-Q9CXC-B9EDX-GWYTY
     *
     * @var string
     */
    protected $hostID;
    /**
     * Option that indicates if the license will be locked to a specific machine
     * using the Zend Host ID code(s). If set to YES, the Host-ID is required.
     *
     * OPTIONAL
     *
     * @var bool
     */
    protected $hardwareLocked;
    /**
     * Semi-colon separated user defined values that will be part of the license. These values
     * CANNOT be modified after the license is produced. Modification
     * would invalidate the license.
     *
     * OPTIONAL
     * Example:
     * Tea=Mint Flavor;Coffee=Arabica
     *
     * @var string
     */
    protected $userDefinedValues;
    /**
     * Semi-colon separated user defined x-values that will be part of the license. These values
     * CAN be modified after the license is produced. Modification
     * won't invalidate the license.
     *
     * OPTIONAL
     * Example:
     * Tea=Mint Flavor;Coffee=Arabica
     *
     * @var string
     */
    protected $xUserDefinedValues;

    /**
     * @param $value
     */
    public function setLicenseTemplate($value)
    {
        $this->licenseTemplate = $value;
    }

    /**
     * @param $productName
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    /**
     * @param $registeredTo
     */
    public function setRegisteredTo($registeredTo)
    {
        $this->registeredTo = $registeredTo;
    }

    /**
     * Process the expires property. If the value is
     * empty (false, '', ...) it will set the value to 'Never'
     * Otherwise it will run the value through strtotime so relative
     * date and time notation can be used (e.g. +1 month)
     *
     * @param mixed $expires
     *
     * @throws BuildException
     * @return string
     */
    public function setExpires($expires)
    {
        // process the expires value
        if (false === $expires || '0' === $expires || strtolower($expires) == 'never' || '' === $expires) {
            $this->expires = 'Never';
        } else {
            $time = strtotime($expires);
            if (!$time) {
                throw new BuildException("Unsupported expires format: " . $expires);
            }
            $this->expires = date('d-M-Y', $time);
        }
    }

    /**
     * @param $iprange
     */
    public function setIpRange($iprange)
    {
        $this->ipRange = $iprange;
    }

    /**
     * @param $hostID
     */
    public function setHostID($hostID)
    {
        $this->hostID = $hostID;
    }

    /**
     * @param $hardwareLocked
     */
    public function setHardwareLocked($hardwareLocked)
    {
        $this->hardwareLocked = (bool) $hardwareLocked;
    }

    /**
     * @param $userDefinedValues
     */
    public function setUserDefinedValues($userDefinedValues)
    {
        $this->userDefinedValues = $userDefinedValues;
    }

    /**
     * @param $xUserDefinedValues
     */
    public function setXUserDefinedValues($xUserDefinedValues)
    {
        $this->xUserDefinedValues = $xUserDefinedValues;
    }

    /**
     * @param $zendsignPath
     */
    public function setZendsignPath($zendsignPath)
    {
        $this->zendsignPath = $zendsignPath;
    }

    /**
     * @param $privateKeyPath
     */
    public function setPrivateKeyPath($privateKeyPath)
    {
        $this->privateKeyPath = $privateKeyPath;
    }

    /**
     * @param $outputFile
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;
    }

    /**
     * Verifies that the configuration is correct
     *
     * @throws BuildException
     */
    protected function verifyConfiguration()
    {
        // Check that the zend encoder path is specified
        if (empty($this->zendsignPath)) {
            throw new BuildException("Zendenc_sign path must be specified");
        }
        // verify that the zend encoder binary exists
        if (!file_exists($this->zendsignPath)) {
            throw new BuildException("Zendenc_sign not found on path " . $this->zendsignPath);
        }

        // verify that the private key path is defined
        if (empty($this->privateKeyPath)) {
            throw new BuildException("You must define privateKeyPath.");
        }
        // verify that the private key file is readable
        if (!is_readable($this->privateKeyPath)) {
            throw new BuildException("Private key file is not readable: " . $this->privateKeyPath);
        }

        // if template is passed, verify that it is readable
        if (!empty($this->licenseTemplate)) {
            if (!is_readable($this->licenseTemplate)) {
                throw new BuildException("License template file is not readable " . $this->licenseTemplate);
            }
        }

        // check that output file path is defined
        if (empty($this->outputFile)) {
            throw new BuildException("Path where to store the result file needs to be defined in outputFile property");
        }

        // if license template is NOT provided check that all required parameters are defined
        if (empty($this->licenseTemplate)) {

            // check productName
            if (empty($this->productName)) {
                throw new BuildException("Property must be defined: productName");
            }

            // check expires
            if (null === $this->expires) {
                throw new BuildException("Property must be defined: expires");
            }

            // check registeredTo
            if (empty($this->registeredTo)) {
                throw new BuildException("Property must be defined: registeredTo");
            }

            // check hardwareLocked
            if (null === $this->hardwareLocked) {
                throw new BuildException("Property must be defined: hardwareLocked");
            }

            // if hardwareLocked is set to true, check that Host-ID is set
            if ($this->hardwareLocked) {
                if (empty($this->hostID)) {
                    throw new BuildException("If you set hardwareLocked to true hostID must be provided");
                }
            }
        }
    }

    /**
     * Do the work
     *
     * @throws BuildException
     */
    public function main()
    {
        try {
            $this->verifyConfiguration();

            $this->generateLicense();
        } catch (Exception $e) {
            // remove the license temp file if it was created
            $this->cleanupTmpFiles();

            throw $e;
        }
        $this->cleanupTmpFiles();
    }

    /**
     * If temporary license file was created during the process
     * this will remove it
     *
     * @return void
     */
    private function cleanupTmpFiles()
    {
        if (!empty($this->tmpLicensePath) && file_exists($this->tmpLicensePath)) {
            $this->log("Deleting temporary license template " . $this->tmpLicensePath, Project::MSG_VERBOSE);

            unlink($this->tmpLicensePath);
        }
    }

    /**
     * Prepares and returns the command that will be
     * used to create the license.
     *
     * @return string
     */
    protected function prepareSignCommand()
    {
        $command = $this->zendsignPath;

        // add license path
        $command .= ' ' . $this->getLicenseTemplatePath();

        // add result file path
        $command .= ' ' . $this->outputFile;

        // add key path
        $command .= ' ' . $this->privateKeyPath;

        $this->zendsignCommand = $command;

        return $command;
    }

    /**
     * Checks if the license template path is defined
     * and returns it.
     * If it the license template path is not defined
     * it will generate a temporary template file and
     * provide it as a template.
     *
     * @return string
     */
    protected function getLicenseTemplatePath()
    {
        if (!empty($this->licenseTemplate)) {
            return $this->licenseTemplate;
        } else {
            return $this->generateLicenseTemplate();
        }
    }

    /**
     * Creates the signed license at the defined output path
     *
     * @throws BuildException
     * @return void
     */
    protected function generateLicense()
    {
        $command = $this->prepareSignCommand() . ' 2>&1';

        $this->log('Creating license at ' . $this->outputFile);

        $this->log('Running: ' . $command, Project::MSG_VERBOSE);
        $tmp = exec($command, $output, $return_var);

        // Check for exit value 1. Zendenc_sign command for some reason
        // returns 0 in case of failure and 1 in case of success...
        if ($return_var !== 1) {
            throw new BuildException("Creating license failed. \n\nZendenc_sign msg:\n" . join("\n", $output) . "\n\n");
        }
    }

    /**
     * It will generate a temporary license template
     * based on the properties defined.
     *
     * @throws BuildException
     * @return string Path of the temporary license template file
     */
    protected function generateLicenseTemplate()
    {
        $this->tmpLicensePath = tempnam(sys_get_temp_dir(), 'zendlicense');

        $this->log("Creating temporary license template " . $this->tmpLicensePath, Project::MSG_VERBOSE);
        if (file_put_contents($this->tmpLicensePath, $this->generateLicenseTemplateContent()) === false) {
            throw new BuildException("Unable to create temporary template license file: " . $this->tmpLicensePath);
        }

        return $this->tmpLicensePath;
    }

    /**
     * Generates license template content based
     * on the defined parameters
     *
     * @return string
     */
    protected function generateLicenseTemplateContent()
    {
        $contentArr = array();

        // Product Name
        $contentArr[] = array('Product-Name', $this->productName);
        // Registered to
        $contentArr[] = array('Registered-To', $this->registeredTo);
        // Hardware locked
        $contentArr[] = array('Hardware-Locked', ($this->hardwareLocked ? 'Yes' : 'No'));

        // Expires
        $contentArr[] = array('Expires', $this->expires);

        // IP-Range
        if (!empty($this->ipRange)) {
            $contentArr[] = array('IP-Range', $this->ipRange);
        }
        // Host-ID
        if (!empty($this->hostID)) {
            foreach (explode(';', $this->hostID) as $hostID) {
                $contentArr[] = array('Host-ID', $hostID);
            }
        } else {
            $contentArr[] = array('Host-ID', 'Not-Locked');
        }

        // parse user defined fields
        if (!empty($this->userDefinedValues)) {
            $this->parseAndAddUserDefinedValues($this->userDefinedValues, $contentArr);
        }
        // parse user defined x-fields
        if (!empty($this->xUserDefinedValues)) {
            $this->parseAndAddUserDefinedValues($this->xUserDefinedValues, $contentArr, 'X-');
        }

        // merge all the values
        $content = '';
        foreach ($contentArr as $valuePair) {

            list($key, $value) = $valuePair;

            $content .= $key . " = " . $value . "\n";
        }

        return $content;
    }

    /**
     * Parse the given string in format like key1=value1;key2=value2;... and
     * converts it to array
     *   (key1=>value1, key2=value2, ...)
     *
     * @param string $valueString Semi-colon separated value pairs
     * @param array $valueArray Array to which the values will be added
     * @param string $keyPrefix Prefix to use when adding the key
     *
     * @param string $pairSeparator
     * @return void
     */
    protected function parseAndAddUserDefinedValues(
        $valueString,
        array &$valueArray,
        $keyPrefix = '',
        $pairSeparator = ';'
    ) {
        // explode the valueString (semicolon)
        $valuePairs = explode($pairSeparator, $valueString);
        if (!empty($valuePairs)) {
            foreach ($valuePairs as $valuePair) {
                list($key, $value) = explode('=', $valuePair, 2);

                // add pair into the valueArray
                $valueArray[] = array($keyPrefix . $key, $value);
            }
        }
    }

}
