<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Transport;

use Zend\Mail\Address;
use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Protocol;
use Zend\Mail\Protocol\Exception as ProtocolException;
use Zend\ServiceManager\ServiceManager;

/**
 * SMTP connection object
 *
 * Loads an instance of Zend\Mail\Protocol\Smtp and forwards smtp transactions
 */
class Smtp implements TransportInterface
{
    /**
     * @var SmtpOptions
     */
    protected $options;

    /**
     * @var Envelope|null
     */
    protected $envelope;

    /**
     * @var Protocol\Smtp
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $autoDisconnect = true;

    /**
     * @var Protocol\SmtpPluginManager
     */
    protected $plugins;

    /**
     * When did we connect to the server?
     *
     * @var int|null
     */
    protected $connectedTime;

    /**
     * Constructor.
     *
     * @param  SmtpOptions $options Optional
     */
    public function __construct(SmtpOptions $options = null)
    {
        if (! $options instanceof SmtpOptions) {
            $options = new SmtpOptions();
        }
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * @param  SmtpOptions $options
     * @return Smtp
     */
    public function setOptions(SmtpOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return SmtpOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options
     *
     * @param  Envelope $envelope
     */
    public function setEnvelope(Envelope $envelope)
    {
        $this->envelope = $envelope;
    }

    /**
     * Get envelope
     *
     * @return Envelope|null
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * Set plugin manager for obtaining SMTP protocol connection
     *
     * @param  Protocol\SmtpPluginManager $plugins
     * @throws Exception\InvalidArgumentException
     * @return Smtp
     */
    public function setPluginManager(Protocol\SmtpPluginManager $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * Get plugin manager for loading SMTP protocol connection
     *
     * @return Protocol\SmtpPluginManager
     */
    public function getPluginManager()
    {
        if (null === $this->plugins) {
            $this->setPluginManager(new Protocol\SmtpPluginManager(new ServiceManager()));
        }
        return $this->plugins;
    }

    /**
     * Set the automatic disconnection when destruct
     *
     * @param  bool $flag
     * @return Smtp
     */
    public function setAutoDisconnect($flag)
    {
        $this->autoDisconnect = (bool) $flag;
        return $this;
    }

    /**
     * Get the automatic disconnection value
     *
     * @return bool
     */
    public function getAutoDisconnect()
    {
        return $this->autoDisconnect;
    }

    /**
     * Return an SMTP connection
     *
     * @param  string $name
     * @param  array|null $options
     * @return Protocol\Smtp
     */
    public function plugin($name, array $options = null)
    {
        return $this->getPluginManager()->get($name, $options);
    }

    /**
     * Class destructor to ensure all open connections are closed
     */
    public function __destruct()
    {
        if (! $this->getConnection() instanceof Protocol\Smtp) {
            return;
        }

        try {
            $this->getConnection()->quit();
        } catch (ProtocolException\ExceptionInterface $e) {
            // ignore
        }

        if ($this->autoDisconnect) {
            $this->getConnection()->disconnect();
        }
    }

    /**
     * Sets the connection protocol instance
     *
     * @param Protocol\AbstractProtocol $connection
     */
    public function setConnection(Protocol\AbstractProtocol $connection)
    {
        $this->connection = $connection;
        if (($connection instanceof Protocol\Smtp)
            && ($this->getOptions()->getConnectionTimeLimit() !== null)
        ) {
            $connection->setUseCompleteQuit(false);
        }
    }

    /**
     * Gets the connection protocol instance
     *
     * @return Protocol\Smtp
     */
    public function getConnection()
    {
        $timeLimit = $this->getOptions()->getConnectionTimeLimit();
        if ($timeLimit !== null
            && $this->connectedTime !== null
            && ((time() - $this->connectedTime) > $timeLimit)
        ) {
            $this->connection = null;
        }
        return $this->connection;
    }

    /**
     * Disconnect the connection protocol instance
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->getConnection() instanceof Protocol\Smtp) {
            $this->getConnection()->disconnect();
            $this->connectedTime = null;
        }
    }

    /**
     * Send an email via the SMTP connection protocol
     *
     * The connection via the protocol adapter is made just-in-time to allow a
     * developer to add a custom adapter if required before mail is sent.
     *
     * @param Message $message
     * @throws Exception\RuntimeException
     */
    public function send(Message $message)
    {
        // If sending multiple messages per session use existing adapter
        $connection = $this->getConnection();

        if (! ($connection instanceof Protocol\Smtp) || ! $connection->hasSession()) {
            $connection = $this->connect();
        } else {
            // Reset connection to ensure reliable transaction
            $connection->rset();
        }

        // Prepare message
        $from       = $this->prepareFromAddress($message);
        $recipients = $this->prepareRecipients($message);
        $headers    = $this->prepareHeaders($message);
        $body       = $this->prepareBody($message);

        if ((count($recipients) == 0) && (! empty($headers) || ! empty($body))) {
            // Per RFC 2821 3.3 (page 18)
            throw new Exception\RuntimeException(
                sprintf(
                    '%s transport expects at least one recipient if the message has at least one header or body',
                    __CLASS__
                )
            );
        }

        // Set sender email address
        $connection->mail($from);

        // Set recipient forward paths
        foreach ($recipients as $recipient) {
            $connection->rcpt($recipient);
        }

        // Issue DATA command to client
        $connection->data($headers . Headers::EOL . $body);
    }

    /**
     * Retrieve email address for envelope FROM
     *
     * @param  Message $message
     * @throws Exception\RuntimeException
     * @return string
     */
    protected function prepareFromAddress(Message $message)
    {
        if ($this->getEnvelope() && $this->getEnvelope()->getFrom()) {
            return $this->getEnvelope()->getFrom();
        }

        $sender = $message->getSender();
        if ($sender instanceof Address\AddressInterface) {
            return $sender->getEmail();
        }

        $from = $message->getFrom();
        if (! count($from)) {
            // Per RFC 2822 3.6
            throw new Exception\RuntimeException(sprintf(
                '%s transport expects either a Sender or at least one From address in the Message; none provided',
                __CLASS__
            ));
        }

        $from->rewind();
        $sender = $from->current();
        return $sender->getEmail();
    }

    /**
     * Prepare array of email address recipients
     *
     * @param  Message $message
     * @return array
     */
    protected function prepareRecipients(Message $message)
    {
        if ($this->getEnvelope() && $this->getEnvelope()->getTo()) {
            return (array) $this->getEnvelope()->getTo();
        }

        $recipients = [];
        foreach ($message->getTo() as $address) {
            $recipients[] = $address->getEmail();
        }
        foreach ($message->getCc() as $address) {
            $recipients[] = $address->getEmail();
        }
        foreach ($message->getBcc() as $address) {
            $recipients[] = $address->getEmail();
        }

        $recipients = array_unique($recipients);
        return $recipients;
    }

    /**
     * Prepare header string from message
     *
     * @param  Message $message
     * @return string
     */
    protected function prepareHeaders(Message $message)
    {
        $headers = clone $message->getHeaders();
        $headers->removeHeader('Bcc');
        return $headers->toString();
    }

    /**
     * Prepare body string from message
     *
     * @param  Message $message
     * @return string
     */
    protected function prepareBody(Message $message)
    {
        return $message->getBodyText();
    }

    /**
     * Lazy load the connection
     *
     * @return Protocol\Smtp
     */
    protected function lazyLoadConnection()
    {
        // Check if authentication is required and determine required class
        $options          = $this->getOptions();
        $config           = $options->getConnectionConfig();
        $config['host']   = $options->getHost();
        $config['port']   = $options->getPort();

        $this->setConnection($this->plugin($options->getConnectionClass(), $config));

        return $this->connect();
    }

    /**
     * Connect the connection, and pass it helo
     *
     * @return Protocol\Smtp
     */
    protected function connect()
    {
        if (! $this->connection instanceof Protocol\Smtp) {
            return $this->lazyLoadConnection();
        }

        $this->connection->connect();

        $this->connectedTime = time();

        $this->connection->helo($this->getOptions()->getName());

        return $this->connection;
    }
}
