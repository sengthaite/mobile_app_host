<?php

function removeDirectory($dir)
{
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    } else {
        return unlink($dir);
    }
}

function isAndroidFileExtension($name): bool
{
    $info = pathinfo($name);
    $fileExtension = $info["extension"];
    if (
        $fileExtension == "apk" ||
        $fileExtension == "xapk" ||
        $fileExtension == "apks" ||
        $fileExtension == "apkm"
    ) {
        return true;
    }
    return false;
}
