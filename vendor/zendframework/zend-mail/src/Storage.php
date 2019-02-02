<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail;

class Storage
{
    // maildir and IMAP flags, using IMAP names, where possible to be able to distinguish between IMAP
    // system flags and other flags
    const FLAG_PASSED   = 'Passed';
    const FLAG_SEEN     = '\Seen';
    const FLAG_UNSEEN   = '\Unseen';
    const FLAG_ANSWERED = '\Answered';
    const FLAG_FLAGGED  = '\Flagged';
    const FLAG_DELETED  = '\Deleted';
    const FLAG_DRAFT    = '\Draft';
    const FLAG_RECENT   = '\Recent';
}
