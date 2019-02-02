<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Storage\Folder;

interface FolderInterface
{
    /**
     * get root folder or given folder
     *
     * @param string $rootFolder get folder structure for given folder, else root
     * @return FolderInterface root or wanted folder
     */
    public function getFolders($rootFolder = null);

    /**
     * select given folder
     *
     * folder must be selectable!
     *
     * @param FolderInterface|string $globalName global name of folder or instance for subfolder
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function selectFolder($globalName);

    /**
     * get Zend\Mail\Storage\Folder instance for current folder
     *
     * @return FolderInterface instance of current folder
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function getCurrentFolder();
}
