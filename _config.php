<?php
//
//use SilverStripe\EnvironmentCheck\EnvironmentCheckSuite;
//
//// These power dev/health, which can be used by load balancers and other such systems
//EnvironmentCheckSuite::register('health', 'DatabaseCheck');
//
//// These power dev/check, which is used for diagnostics and for deployment
//EnvironmentCheckSuite::register('check', 'DatabaseCheck("Member")', "Is the database accessible?");
//EnvironmentCheckSuite::register('check', 'URLCheck("")', "Is the homepage accessible?");
//
//EnvironmentCheckSuite::register(
//    'check',
//    'HasFunctionCheck("curl_init")',
//    "Does PHP have CURL support?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'HasFunctionCheck("imagecreatetruecolor")',
//    "Does PHP have GD2 support?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'HasFunctionCheck("xml_set_object")',
//    "Does PHP have XML support?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'HasFunctionCheck("token_get_all")',
//    "Does PHP have tokenizer support?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'HasFunctionCheck("iconv")',
//    "Does PHP have iconv support?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'HasFunctionCheck("hash")',
//    "Does PHP have hash support?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'HasFunctionCheck("session_start")',
//    "Does PHP have session support?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'HasClassCheck("DOMDocument")',
//    "Does PHP have DOMDocument support?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'FileWriteableCheck("assets")',
//    "Is assets/ writeable?"
//);
//
//EnvironmentCheckSuite::register(
//    'check',
//    'FileWriteableCheck("' . TEMP_FOLDER . '")',
//    "Is the temp folder writeable?"
//);
