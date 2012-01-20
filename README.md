# SilverStripe Environment Checker Module

Developed by Sam Minnée, thanks to Will Rossiter.

This module adds an API for running environment checks to your API.

 * `dev/health` - A public URL that performs a quick check that this environment is functioning.  This could be tied to a load balancer, for example.
 * `dev/check` - An admin-only URL that performs a more comprehensive set of checks.  This could be tied to a deployment system, for example.

## Aren't these just unit tests?

Almost, but not really. Environment checks differ from unit tests in two important ways:

 * **They test environment specific settings.** Unit tests are designed to use dummy data and mock interfaces to external system.  Environment checks check the real systems and data that the given environment is actually connected to.
 * **They can't modify data.** Because these checks will run using production databases, they can't go modifying the data in there. This is the biggest reason why we haven't used the same base class as a unit test for writing environment checks - we wanted to make it impossible to accidentally plug a unit test into the environment checker!

## Adding more checks

To add more checks, you should put additional `EnvironmentCheckSuite::register` calls into your `_config.php`.  See the `_config.php` file of this module for examples.

	:::php
	EnvironmentCheckSuite::register('check', 'HasFunctionCheck("curl_init")', "Does PHP have CURL support?");
	EnvironmentCheckSuite::register('check', 'HasFunctionCheck("imagecreatetruecolor")', "Does PHP have GD2 support?");
	
The first argument is the name of the check suite.  There are two built-in check suites, "health", and "check", corresponding to the `dev/health` and `dev/check` URLs.  If you wish, you can create your own check suites and execute them on other URLs.

The module comes bundled with a few checks in `DefaultHealthChecks.php`.  However, to test your own application, you probably want to write custom checks.

 * Implement the `EnvironmentCheck` interface
 * Define the `check()` function, which returns a 2 element array:
   * The first element is one of `EnvironmentCheck::OK`, `EnvironmentCheck::WARNING`, `EnvironmentCheck::ERROR`, depending on the status of the check
   * The second element is a string describing the response.

Here is a simple example of how you might create a check to test your own code.  In this example, we are checking that an instance of the `MyGateway` class will return "foo" when `call()` is called on it.  Testing interfaces with 3rd party systems is a common use case for custom environment checks.

	:::php
	class MyGatewayCheck implements EnvironmentCheck {
		protected $checkTable;

		function check() {
			$g = new MyGateway;
			
			$response = $g->call();
			$expectedResponse = "foo";
			
			if($response == null) {
				return array(EnvironmentCheck::ERROR, "MyGateway didn't return a response");
			} else if($response != $expectedResponse) {
				return array(EnvironmentCheck::WARNING, "MyGateway returned unexpected response $response");
			} else {
				return array(EnvironmentCheck::OK, "");
			}
		}
	}
	
Once you have created your custom check class, don't forget to register it in a check suite
	
	:::php
	EnvironmentCheckSuite::register('check', 'MyGatewayCheck', "Can I connect to the gateway?");

### Using other environment check suites

If you want to use the same UI as dev/health and dev/check, you can create an `EnvironmentChecker` object.  This class is a `RequestHandler` and so can be returned from an action handler.  The first argument to the `EnvironmentChecker` constructor is the suite name.  For example:

	class DevHealth extends Controller {
		function index() {
			$e = new EnvironmentChecker('health', 'Site health');
			return $e;
		}
	}
	
If you wish to embed an environment check suite in another, you can use the following call.

	$result = EnvironmentCheckSuite::inst("health")->run();
	
`$result` will contain a `EnvironmentCheckSuiteResult` object

 * `$result->ShouldPass()`: Return a boolean of whether or not the tests passed.
 * `$result->Status()`: The string "OK", "WARNING", or "ERROR", depending on the worst failure.
 * `$result->Details()`: A `DataObjectSet` of details about the result of each check in the suite.

See `EnvironmentChecker.ss` to see how these can be used to build a UI.
