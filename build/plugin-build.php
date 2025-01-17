#!/usr/bin/php
<?php
if ($argc < 2) {
    die("Usage: " . basename($argv[0]) . " <version>\nRun from the folder containing the build config files.\n");
}

$version = $argv[1];

$publish = false;
foreach ($argv as $arg) {
    if ('-p' === $arg) {
        $publish = true;
    }
}

$build_cfg_dir = getcwd();
$parent_dir = dirname($build_cfg_dir);

// Defaults - override in config.php
$src_dir = $parent_dir;
$zip_dir = $build_cfg_dir;
$tmp_dir = "$build_cfg_dir/tmp";
$cleanup = true;

$config_file = "$build_cfg_dir/config.php";
if (!file_exists($config_file)) {
    echo "Could not find the config.php file in the current folder.\n";
    exit;
}

include $config_file;

if (!isset($plugin_slug)) {
    $plugin_slug = basename($src_dir);
}

$dest_dir = "$tmp_dir/$plugin_slug";

if (!isset($main_plugin_file)) {
    $main_plugin_file = "$src_dir/$plugin_slug.php";
}

if (!file_exists($main_plugin_file)) {
    echo "Cannot find main plugin file $main_plugin_file.\n";
    exit;
}

/*
 * version-check.php example:
 * $version_checks = array(
 * 		"$plugin_slug.php" => array(
 * 			'@Version:\s+(.*)\n@' => 'header'
 * 		)
 * );
 *
*/
$version_check_hook = "$build_cfg_dir/version-check.php";
if (file_exists($version_check_hook)) {
    include $version_check_hook;

    $messages = '';

    foreach ($version_checks as $file => $regexes) {
        $file = "$src_dir/$file";

        if (!file_exists($file)) {
            $messages .= "Whoa! Couldn't find $file\n";
            continue;
        }

        $file_content = file_get_contents($file);

        if (!$file_content) {
            $messages .= "Whoa! Could not read contents of $file\n";
            continue;
        }

        foreach ($regexes as $regex => $context) {
            if (!preg_match($regex, $file_content, $matches)) {
                $messages .= "Whoa! Couldn't find $context version number in $file\n";
                continue;
            }

            if (trim($matches[1]) != trim($version)) {
                $messages .= "Whoa! " . ucfirst($context) . " version number is currently $matches[1] in $file\n";
            }
        }
    }

    if ($messages) {
        die($messages);
    }
}

`rm -Rf "$dest_dir" 2>/dev/null`;

if (!is_dir($tmp_dir)) {
    mkdir($tmp_dir);
}

mkdir($dest_dir);

$filter_file = "$build_cfg_dir/filter";

// Use this hook to override the default filter file
$filter_hook = "$build_cfg_dir/filter.php";
if (file_exists($filter_hook)) {
    include $filter_hook;
}

`rsync -r --filter=". $filter_file" "$src_dir"/* "$dest_dir"`;

chdir($tmp_dir);

// Use this hook to do anything before we zip up
$pre_zip_hook = "$build_cfg_dir/pre-zip.php";
if (file_exists($pre_zip_hook)) {
    include $pre_zip_hook;
}

if (!isset($zip_name)) {
    $zip_name = "$plugin_slug-$version.zip";
} else {
    $zip_name = "$zip_name-$version.zip";
}

echo `zip -r $zip_name $plugin_slug -x "*.DS_Store"`;

if ($zip_dir != $tmp_dir) {
    `mv $zip_name "$zip_dir"/`;
}

if ($cleanup) {
    `rm -Rf "$dest_dir"`;
}