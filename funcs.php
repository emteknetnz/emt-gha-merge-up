<?php

function versions(
    string $defaultBranch,
    string $minimumCmsMajor,
    // The following params are purely for unit testing, for the actual github action it will read json files instead
    string $composerJson = '',
    string $tagsJson = '',
    string $branchesJson = ''
) {
    if (!is_numeric($defaultBranch)) {
        // @todo: confirm these will immediately fail the github actions job
        // if not, try echo + exit(1)
        throw new Exception('Default branch must be a number');
    }
    if (!ctype_digit($minimumCmsMajor)) {
        throw new Exception('Minimum CMS major must be an integer');
    }

    // work out default major
    preg_match('#^([0-9]+)+\.?[0-9]*$#', $defaultBranch, $matches);
    $defaultMajor = $matches[1];
    
    // read composer.json of the current (default) branch
    $contents = $composerJson ?: file_get_contents('composer.json');
    $json = json_decode($contents);
    $defaultCmsMajor = '';
    $version = preg_replace('#[^0-9\.]#', '', $json->require->{'silverstripe/framework'} ?? '');
    if (preg_match('#^([0-9]+)+\.?[0-9]*$#', $version, $matches)) {
        $defaultCmsMajor = $matches[1];
    } else {
        $phpVersion = $json->require->{'php'} ?? '';
        if (substr($phpVersion,0, 4) === '^7.4') {
            $defaultCmsMajor = 4;
        } elseif (substr($phpVersion,0, 4) === '^8.1') {
            $defaultCmsMajor = 5;
        }
    }
    if ($defaultCmsMajor === '') {
        throw new Exception('Could not work out what the default CMS major version this module uses');
    }
    // work out major diff e.g for silverstripe/admin for CMS 5 => 5 - 2 = 3
    $majorDiff = $defaultCmsMajor - $defaultMajor;

    $minorsWithStableTags = [];
    $contents = $tagsJson ?: file_get_contents('__tags.json');
    foreach (json_decode($contents) as $row) {
        $tag = $row->name;
        if (!preg_match('#^([0-9]+)\.([0-9]+)\.([0-9]+)$#', $tag, $matches)) {
            continue;
        }
        $minor = $matches[1] . '.' . $matches[2];
        $minorsWithStableTags[] = $minor;
    }

    $branches = [];
    $contents = $branchesJson ?: file_get_contents('__branches.json');
    foreach (json_decode($contents) as $row) {
        $branch = $row->name;
        // filter out non-standard branches
        if (!preg_match('#^([0-9]+)+\.?[0-9]*$#', $branch, $matches)) {
            continue;
        }
        // filter out majors that are too old
        $major = $matches[1];
        if (($major + $majorDiff) < $minimumCmsMajor) {
            continue;
        }
        // filter out minor branches that are pre-stable
        if (preg_match('#^([0-9]+)\.([0-9]+)$#', $branch, $matches)) {
            $minor = $matches[1] . '.' . $matches[2];
            if (!in_array($branch, $minorsWithStableTags)) {
                continue;
            }
        }
        // suffix a .999 minor version to major branches
        if (preg_match('#^[0-9]+$#', $branch)) {
            $branch .= '.999';
        }
        $branches[] = $branch;
    }
    
    // sort so that newest is first
    usort($branches, 'version_compare');
    $branches = array_reverse($branches);
    
    // remove the .999
    array_walk($branches, function(&$branch) {
        $branch = preg_replace('#\.999$#', '', $branch);
    });
    
    // remove everything except the latest minor from each major line
    $foundMinorInMajors = [];
    foreach ($branches as $i => $branch) {
        if (!preg_match('#^([0-9]+)\.[0-9]+$#', $branch, $matches)) {
            continue;
        }
        $major = $matches[1];
        if (isset($foundMinorInMajors[$major])) {
            unset($branches[$i]);
            continue;
        }
        $foundMinorInMajors[$major] = $branch;
    }
    
    // reverse the array so that oldest is first
    $branches = array_reverse($branches);
    
    return $branches;
}
