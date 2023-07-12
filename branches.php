<?php

include 'funcs.php';

$defaultBranch = getenv('DEFAULT_BRANCH');
$minimumCmsMajor = getenv('MINIMUM_CMS_MAJOR');

$branches = [];

// using this try/catch block to echo a string on exception because bash is calling
// this within the context of BRANCHES=$(php branches.php) which means that while the exception
// will halt the github action, it will not echo the exception to the github action log
try {
    $branches = branches($defaultBranch, $minimumCmsMajor);
} catch (Exception $e) {
    echo 'EXCEPTION - ' . $e->getMessage();
    exit(0);
}

echo implode(' ', $branches);
