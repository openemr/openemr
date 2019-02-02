<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for feed reader extensions based on the
 * AbstractPluginManager.
 *
 * Validation checks that we have an Extension\AbstractEntry or
 * Extension\AbstractFeed.
 */
class ExtensionPluginManager extends AbstractPluginManager implements ExtensionManagerInterface
{
    /**
     * Aliases for default set of extension classes
     *
     * @var array
     */
    protected $aliases = [
        'atomentry'            => Extension\Atom\Entry::class,
        'atomEntry'            => Extension\Atom\Entry::class,
        'AtomEntry'            => Extension\Atom\Entry::class,
        'Atom\Entry'           => Extension\Atom\Entry::class,
        'atomfeed'             => Extension\Atom\Feed::class,
        'atomFeed'             => Extension\Atom\Feed::class,
        'AtomFeed'             => Extension\Atom\Feed::class,
        'Atom\Feed'            => Extension\Atom\Feed::class,
        'contententry'         => Extension\Content\Entry::class,
        'contentEntry'         => Extension\Content\Entry::class,
        'ContentEntry'         => Extension\Content\Entry::class,
        'Content\Entry'        => Extension\Content\Entry::class,
        'creativecommonsentry' => Extension\CreativeCommons\Entry::class,
        'creativeCommonsEntry' => Extension\CreativeCommons\Entry::class,
        'CreativeCommonsEntry' => Extension\CreativeCommons\Entry::class,
        'CreativeCommons\Entry' => Extension\CreativeCommons\Entry::class,
        'creativecommonsfeed'  => Extension\CreativeCommons\Feed::class,
        'creativeCommonsFeed'  => Extension\CreativeCommons\Feed::class,
        'CreativeCommonsFeed'  => Extension\CreativeCommons\Feed::class,
        'CreativeCommons\Feed' => Extension\CreativeCommons\Feed::class,
        'dublincoreentry'      => Extension\DublinCore\Entry::class,
        'dublinCoreEntry'      => Extension\DublinCore\Entry::class,
        'DublinCoreEntry'      => Extension\DublinCore\Entry::class,
        'DublinCore\Entry'     => Extension\DublinCore\Entry::class,
        'dublincorefeed'       => Extension\DublinCore\Feed::class,
        'dublinCoreFeed'       => Extension\DublinCore\Feed::class,
        'DublinCoreFeed'       => Extension\DublinCore\Feed::class,
        'DublinCore\Feed'      => Extension\DublinCore\Feed::class,
        'googleplaypodcastentry'  => Extension\GooglePlayPodcast\Entry::class,
        'googlePlayPodcastEntry'  => Extension\GooglePlayPodcast\Entry::class,
        'GooglePlayPodcastEntry'  => Extension\GooglePlayPodcast\Entry::class,
        'GooglePlayPodcast\Entry' => Extension\GooglePlayPodcast\Entry::class,
        'googleplaypodcastfeed'   => Extension\GooglePlayPodcast\Feed::class,
        'googlePlayPodcastFeed'   => Extension\GooglePlayPodcast\Feed::class,
        'GooglePlayPodcastFeed'   => Extension\GooglePlayPodcast\Feed::class,
        'GooglePlayPodcast\Feed'  => Extension\GooglePlayPodcast\Feed::class,
        'podcastentry'         => Extension\Podcast\Entry::class,
        'podcastEntry'         => Extension\Podcast\Entry::class,
        'PodcastEntry'         => Extension\Podcast\Entry::class,
        'Podcast\Entry'        => Extension\Podcast\Entry::class,
        'podcastfeed'          => Extension\Podcast\Feed::class,
        'podcastFeed'          => Extension\Podcast\Feed::class,
        'PodcastFeed'          => Extension\Podcast\Feed::class,
        'Podcast\Feed'         => Extension\Podcast\Feed::class,
        'slashentry'           => Extension\Slash\Entry::class,
        'slashEntry'           => Extension\Slash\Entry::class,
        'SlashEntry'           => Extension\Slash\Entry::class,
        'Slash\Entry'          => Extension\Slash\Entry::class,
        'syndicationfeed'      => Extension\Syndication\Feed::class,
        'syndicationFeed'      => Extension\Syndication\Feed::class,
        'SyndicationFeed'      => Extension\Syndication\Feed::class,
        'Syndication\Feed'     => Extension\Syndication\Feed::class,
        'threadentry'          => Extension\Thread\Entry::class,
        'threadEntry'          => Extension\Thread\Entry::class,
        'ThreadEntry'          => Extension\Thread\Entry::class,
        'Thread\Entry'         => Extension\Thread\Entry::class,
        'wellformedwebentry'   => Extension\WellFormedWeb\Entry::class,
        'wellFormedWebEntry'   => Extension\WellFormedWeb\Entry::class,
        'WellFormedWebEntry'   => Extension\WellFormedWeb\Entry::class,
        'WellFormedWeb\Entry'  => Extension\WellFormedWeb\Entry::class,
    ];

    /**
     * Factories for default set of extension classes
     *
     * @var array
     */
    protected $factories = [
        Extension\Atom\Entry::class            => InvokableFactory::class,
        Extension\Atom\Feed::class             => InvokableFactory::class,
        Extension\Content\Entry::class         => InvokableFactory::class,
        Extension\CreativeCommons\Entry::class => InvokableFactory::class,
        Extension\CreativeCommons\Feed::class  => InvokableFactory::class,
        Extension\DublinCore\Entry::class      => InvokableFactory::class,
        Extension\DublinCore\Feed::class       => InvokableFactory::class,
        Extension\GooglePlayPodcast\Entry::class => InvokableFactory::class,
        Extension\GooglePlayPodcast\Feed::class  => InvokableFactory::class,
        Extension\Podcast\Entry::class         => InvokableFactory::class,
        Extension\Podcast\Feed::class          => InvokableFactory::class,
        Extension\Slash\Entry::class           => InvokableFactory::class,
        Extension\Syndication\Feed::class      => InvokableFactory::class,
        Extension\Thread\Entry::class          => InvokableFactory::class,
        Extension\WellFormedWeb\Entry::class   => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'zendfeedreaderextensionatomentry'            => InvokableFactory::class,
        'zendfeedreaderextensionatomfeed'             => InvokableFactory::class,
        'zendfeedreaderextensioncontententry'         => InvokableFactory::class,
        'zendfeedreaderextensioncreativecommonsentry' => InvokableFactory::class,
        'zendfeedreaderextensioncreativecommonsfeed'  => InvokableFactory::class,
        'zendfeedreaderextensiondublincoreentry'      => InvokableFactory::class,
        'zendfeedreaderextensiondublincorefeed'       => InvokableFactory::class,
        'zendfeedreaderextensiongoogleplaypodcastentry' => InvokableFactory::class,
        'zendfeedreaderextensiongoogleplaypodcastfeed'  => InvokableFactory::class,
        'zendfeedreaderextensionpodcastentry'         => InvokableFactory::class,
        'zendfeedreaderextensionpodcastfeed'          => InvokableFactory::class,
        'zendfeedreaderextensionslashentry'           => InvokableFactory::class,
        'zendfeedreaderextensionsyndicationfeed'      => InvokableFactory::class,
        'zendfeedreaderextensionthreadentry'          => InvokableFactory::class,
        'zendfeedreaderextensionwellformedwebentry'   => InvokableFactory::class,
    ];

    /**
     * Do not share instances (v2)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Do not share instances (v3)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the extension loaded is of a valid type.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validate($plugin)
    {
        if ($plugin instanceof Extension\AbstractEntry
            || $plugin instanceof Extension\AbstractFeed
        ) {
            // we're okay
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Extension\AbstractFeed '
            . 'or %s\Extension\AbstractEntry',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__,
            __NAMESPACE__
        ));
    }

    /**
     * Validate the plugin (v2)
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Plugin of type %s is invalid; must implement %s\Extension\AbstractFeed '
                . 'or %s\Extension\AbstractEntry',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                __NAMESPACE__,
                __NAMESPACE__
            ));
        }
    }
}
