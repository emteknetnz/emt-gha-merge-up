<?php

include 'funcs.php';

$defaultBranch = getenv('DEFAULT_BRANCH');
$minimumCmsMajor = getenv('MINIMUM_CMS_MAJOR');

// using handler to echo a string on error/exception because bash is calling
// this within the context of BRANCHES=$(php branches.php) which means that while the error/exception
// will halt the github action, it will not echo the error/exception to the github action log
// function handler(Throwable $exception) {
//     echo "FAILURE - " , $exception->getMessage(), "\n";
// }
// set_exception_handler('handler');
// set_error_handler('handler');

$branches = branches($defaultBranch, $minimumCmsMajor);
echo implode(' ', $branches);
