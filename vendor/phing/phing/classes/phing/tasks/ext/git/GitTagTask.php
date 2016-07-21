<?php
/*
 *  $Id: d01c0f1426d0075ae04ff7c156452d08909e966a $
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

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper around git-tag
 *
 * @author Evan Kaufman <evan@digitalflophouse.com>
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id: d01c0f1426d0075ae04ff7c156452d08909e966a $
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.5
 */
class GitTagTask extends GitBaseTask
{
    /**
     * Make unsigned, annotated tag object. See -a of git-tag
     * @var boolean
     */
    private $annotate = false;

    /**
     * Make GPG-signed tag. See -s of git-tag
     * @var boolean
     */
    private $sign = false;

    /**
     * Make GPG-signed tag, using given key. See -u of git-tag
     * @var string
     */
    private $keySign;

    /**
     * Replace existing tag with given name. See -f of git-tag
     * @var boolean
     */
    private $replace = false;

    /**
     * Delete existing tags with given names. See -d of git-tag
     * @var boolean
     */
    private $delete = false;

    /**
     * Verify gpg signature of given tag names. See -v of git-tag
     * @var boolean
     */
    private $verify = false;

    /**
     * List tags with names matching given pattern. See -l of git-tag
     * @var boolean
     */
    private $list = false;

    /**
     * <num> specifies how many lines from the annotation, if any, are printed
     * when using -l. See -n of git-tag
     * @var int
     */
    private $num;

    /**
     * Only list tags containing specified commit. See --contains of git-tag
     * @var string
     */
    private $contains;

    /**
     * Use given tag message. See -m of git-tag
     * @var string
     */
    private $message;

    /**
     * Take tag message from given file. See -F of git-tag
     * @var string
     */
    private $file;

    /**
     * <tagname> argument to git-tag
     * @var string
     */
    private $name;

    /**
     * <commit> argument to git-tag
     * @var string
     */
    private $commit;

    /**
     * <object> argument to git-tag
     * @var string
     */
    private $object;

    /**
     * <pattern> argument to git-tag
     * @var string
     */
    private $pattern;

    /**
     * Property name to set with output value from git-tag
     * @var string
     */
    private $outputProperty;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('tag');
        $command
            ->setOption('a', $this->isAnnotate())
            ->setOption('s', $this->isSign())
            ->setOption('f', $this->isReplace())
            ->setOption('d', $this->isDelete())
            ->setOption('v', $this->isVerify())
            ->setOption('l', $this->isList());

        if (null !== $this->getKeySign()) {
            $command->setOption('u', $this->getKeySign());
        }

        if (null !== $this->getMessage()) {
            $command->setOption('m', $this->getMessage());
        }

        if (null !== $this->getFile()) {
            $command->setOption('F', $this->getFile());
        }

        // Use 'name' arg, if relevant
        if (null != $this->getName() && false == $this->isList()) {
            $command->addArgument($this->getName());
        }

        if (null !== $this->getKeySign() || $this->isAnnotate() || $this->isSign()) {
            // Require a tag message or file
            if (null === $this->getMessage() && null === $this->getFile()) {
                throw new BuildException('"message" or "file" required to make a tag');
            }
        }

        // Use 'commit' or 'object' args, if relevant
        if (null !== $this->getCommit()) {
            $command->addArgument($this->getCommit());
        } else {
            if (null !== $this->getObject()) {
                $command->addArgument($this->getObject());
            }
        }

        // Customize list (-l) options
        if ($this->isList()) {
            if (null !== $this->getContains()) {
                $command->setOption('contains', $this->getContains());
            }
            if (null !== $this->getPattern()) {
                $command->addArgument($this->getPattern());
            }
            if (null != $this->getNum()) {
                $command->setOption('n', $this->getNum());
            }
        }

        $this->log('git-tag command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            $this->log($e->getMessage(), Project::MSG_ERR);
            throw new BuildException('Task execution failed. ' . $e->getMessage());
        }

        if (null !== $this->outputProperty) {
            $this->project->setProperty($this->outputProperty, $output);
        }

        $this->log(
            sprintf('git-tag: tags for "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-tag output: ' . trim($output), Project::MSG_INFO);
    }

    /**
     * @param $flag
     */
    public function setAnnotate($flag)
    {
        $this->annotate = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getAnnotate()
    {
        return $this->annotate;
    }

    /**
     * @return bool
     */
    public function isAnnotate()
    {
        return $this->getAnnotate();
    }

    /**
     * @param $flag
     */
    public function setSign($flag)
    {
        $this->sign = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @return bool
     */
    public function isSign()
    {
        return $this->getSign();
    }

    /**
     * @param $keyId
     */
    public function setKeySign($keyId)
    {
        $this->keySign = $keyId;
    }

    /**
     * @return string
     */
    public function getKeySign()
    {
        return $this->keySign;
    }

    /**
     * @param $flag
     */
    public function setReplace($flag)
    {
        $this->replace = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getReplace()
    {
        return $this->replace;
    }

    /**
     * @return bool
     */
    public function isReplace()
    {
        return $this->getReplace();
    }

    /**
     * @param $flag
     */
    public function setForce($flag)
    {
        return $this->setReplace($flag);
    }

    /**
     * @param $flag
     */
    public function setDelete($flag)
    {
        $this->delete = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->getDelete();
    }

    /**
     * @param $flag
     */
    public function setVerify($flag)
    {
        $this->verify = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getVerify()
    {
        return $this->verify;
    }

    /**
     * @return bool
     */
    public function isVerify()
    {
        return $this->getVerify();
    }

    /**
     * @param $flag
     */
    public function setList($flag)
    {
        $this->list = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @return bool
     */
    public function isList()
    {
        return $this->getList();
    }

    /**
     * @param $num
     */
    public function setNum($num)
    {
        $this->num = (int) $num;
    }

    /**
     * @return int
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * @param $commit
     */
    public function setContains($commit)
    {
        $this->contains = $commit;
    }

    /**
     * @return string
     */
    public function getContains()
    {
        return $this->contains;
    }

    /**
     * @param $msg
     */
    public function setMessage($msg)
    {
        $this->message = $msg;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $commit
     */
    public function setCommit($commit)
    {
        $this->commit = $commit;
    }

    /**
     * @return string
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param $pattern
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param $prop
     */
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }
}
