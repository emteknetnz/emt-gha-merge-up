<?php

// @todo this needs to be an environment variable so is globally configurable from gha-merge-up
$minimumMajor = 4;

// read composer.json of the default branch
$contents = file_get_contents('composer.json');
// see version of framework that's required

// if that doesn't exist then guess based on version of module
// silverstripe/config => 2 = cms 5, 1 = cms 4 ... 3 = cms 6, 4 = cms 7
// otherwise just hard fail the job
// @todo confirm that `php exit 1` will indeed fail a github action job - see gha-generate-matrix

$contents = file_get_contents('__response.json');
$json = json_decode($contents);
$branches = [];
foreach ($json as $row) {
    $branch = $row->name;
    if (!preg_match('#^[0-9]+\.?[0-9]*$#', $branch)) {
        continue;
    }
    $branches[] = $branch;
}
natsort($branches);
$branches = array_reverse($branches);

if (empty($branches)) {
    echo "No standard branches found\n";
    die;
}

preg_match('#^([0-9]+)\.?[0-9]*$#', $branches[0], $matches);
$currentMajor = $matches[1];
echo "currentMajor is $currentMajor\n";

if ($includePreviousMajor) {
    $previousMajor = $currentMajor - 1;
    $previousMajorExists = false;
    foreach ($branches as $branch) {
        if (preg_match('#^' . $previousMajor . '\.?[0-9]*$#', $branch)) {
            $previousMajorExists = true;
            break;
        }
    }
    echo "previousMajorExists is " . ($previousMajorExists ? 'true' : 'false') . "\n";
    $previousMajor
}
