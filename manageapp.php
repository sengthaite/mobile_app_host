<?php

require_once 'utils.php';
require_once 'constants.php';

class ManageApp
{

    function addApp($zip, $sprintdir)
    {
        if (!isset($sprintdir)) {
            return;
        }
        $zipname = TMP_DIR . "/" . $zip['name'];

        $zipArchive = new ZipArchive;
        $zipFileName = pathinfo($zip['name'])['filename'];
        if ($zipArchive->open($zipname) === TRUE && isset($zipFileName)) {
            $zipArchive->extractTo(ASSETS . "/" . $sprintdir);
            $dir = ASSETS . "/" . $sprintdir . "/" . $zipFileName;
            $files = glob($dir . "/*.ipa");
            if (count($files) == 1) {
                rename($files[0], $dir . "/app.ipa");
            }
        } else {
            echo "Error: Not a zip file";
        }
        unlink($zipname);
        $zipArchive->close();
    }

    function removeApp($app_path, $sprintdir)
    {
        if (!isset($sprintdir)) {
            return;
        }
        $path = ASSETS . "/" . $sprintdir . "/" . $app_path;
        removeDirectory($path);
    }
}
