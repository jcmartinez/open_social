<?php

require_once __DIR__.'../../../../../../vendor/autoload.php';

use Blackfire\LoopClient;
use Blackfire\Client;
use Blackfire\Profile\Configuration as ProfileConfiguration;

// Grab the values from the command line so we can push any data to github.
$commandLine = getopt(null, ["commit:uuid"]);
$uuid = $commandLine['uuid'];
$commit = $commandLine['commit'];

if (empty($uuid) || empty($commit)) {
  return 0;
}

$config = new \Blackfire\Profile\Configuration();
$blackfire = new \Blackfire\Client();
$probe = $blackfire->createProbe($config);

// Create a build that will show up on our dashboard in blackfire.
$build = $blackfire->startBuild($uuid, array(
  'title' => 'Build from GitHub Commit: ' . $commit,
  'external_id' => $commit,
  'trigger_name' => 'Travis CI - Commit: ' . $commit,
));

// create a scenario (if the $build argument is null, a new build will be created)
$scenario = $blackfire->startScenario($build, array(
  'title' => 'Test to see if it works.',
));

// create a configuration
$config = new \Blackfire\Profile\Configuration();
$config->setScenario($scenario);

// create as many profiles as you need
$probe = $blackfire->createProbe($config);

// TEST STARTS HERE.
$request = $blackfire->createRequest('Homepage');
$header = 'X-Blackfire-Query: '.$request->getToken();
// TEST ENDS HERE.

$blackfire->endProbe($probe);

// end the scenario and fetch the report
$report = $blackfire->closeScenario($scenario);

// end the build
$blackfire->closeBuild($build);

// Output the URL if we have any.
$profile = $blackfire->getProfile($uuid);
$url = $profile->getUrl();
sprintf('The URL of your build can be found here: %s', $url);