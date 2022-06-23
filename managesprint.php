<?php

require_once 'utils.php';
require_once 'constants.php';

class ManageSprint
{

    function __construct()
    {
        if (!is_dir(ASSETS)) {
            mkdir(ASSETS);
        }
    }

    function addSprint($name)
    {
        $stripName = strtolower(str_replace(' ', '', $name));
        if (!is_dir(ASSETS . "/" . $stripName)) {
            mkdir(ASSETS . "/" . $stripName);
        }
    }

    function removeSprint($name)
    {
        removeDirectory(ASSETS . "/" . $name);
    }
}
