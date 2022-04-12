<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use SilverStripe\EnvironmentCheck\EnvironmentCheck;

/**
 * Check that the given file is writable.
 *
 * @package environmentcheck
 */
class FileWriteableCheck implements EnvironmentCheck
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $path The full path. If a relative path, it will relative to the BASE_PATH.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function check()
    {
        if ($this->path[0] == '/') {
            $filename = $this->path;
        } else {
            $filename = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $this->path ?? '');
        }

        if (file_exists($filename ?? '')) {
            $isWriteable = is_writeable($filename ?? '');
        } else {
            $isWriteable = is_writeable(dirname($filename ?? ''));
        }

        if (!$isWriteable) {
            if (function_exists('posix_getgroups')) {
                $userID = posix_geteuid();
                $user = posix_getpwuid($userID ?? 0);

                $currentOwnerID = fileowner(file_exists($filename ?? '') ? $filename : dirname($filename ?? ''));
                $currentOwner = posix_getpwuid($currentOwnerID ?? 0);

                $message = "User '$user[name]' needs to be able to write to this file:\n$filename\n\nThe file is "
                    . "currently owned by '$currentOwner[name]'.  ";

                if ($user['name'] == $currentOwner['name']) {
                    $message .= 'We recommend that you make the file writeable.';
                } else {
                    $groups = posix_getgroups();
                    $groupList = [];
                    foreach ($groups as $group) {
                        $groupInfo = posix_getgrgid($group ?? 0);
                        if (in_array($currentOwner['name'], $groupInfo['members'] ?? [])) {
                            $groupList[] = $groupInfo['name'];
                        }
                    }
                    if ($groupList) {
                        $message .= "	We recommend that you make the file group-writeable and change the group to "
                            . "one of these groups:\n - " . implode("\n - ", $groupList)
                            . "\n\nFor example:\nchmod g+w $filename\nchgrp " . $groupList[0] . " $filename";
                    } else {
                        $message .= "  There is no user-group that contains both the web-server user and the owner "
                            . "of this file.  Change the ownership of the file, create a new group, or temporarily "
                            . "make the file writeable by everyone during the install process.";
                    }
                }
            } else {
                $message = "The webserver user needs to be able to write to this file:\n$filename";
            }

            return [EnvironmentCheck::ERROR, $message];
        }

        return [EnvironmentCheck::OK, ''];
    }
}
