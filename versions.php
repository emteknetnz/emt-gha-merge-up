<?php

include 'funcs.php';

$defaultBranch = getenv('DEFAULT_BRANCH');
$minimumCmsMajor = getenv('MINIMUM_CMS_MAJOR');

echo implode(' ', versions($defaultBranch, $minimumCmsMajor));
