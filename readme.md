flightaware-php-client
======================

This class allows for quick interaction with a (small) subset of FlightAware's FlightXML REST API. See http://flightaware.com/commercial/flightxml/explorer/ for more information.

Requirements
------------

1. PHP 5.3+ (tested with 5.4.24)
2. curl
3. A FlightAware API account

Usage
-----

First, require FlightAwareClient.php. Then instantiate the client class as follows:

````
$faClient = new FlightAwareClient('username', 'api_key');
````

The following methods are available; all return unwrapped result arrays...

````
$inFlightInfo = $faClient->inFlightInfo($ident); // reformats waypoints with distinct latitude and longitude array keys
$flightInfoEx = $faClient->flightInfoEx($ident, $how_many, $offset);
$flightId = $faClient->getFlightId($ident, $departure_time);
$historicalTrack = $faClient->getHistoricalTrack($flight_id);
$lastTrack = $faClient->getLastTrack($ident);
$endpoint = $faClient->registerAlertEndpoint($url,$format);
$getAlertResults = $faClient->getAlerts()
$newAlertID = $faClient->setAlert($alert_id, $ident, $origin, $destination, $aircrafttype, $date_start, $date_end, $channels, $enablede, $max_weekly);
$foo = $faClient->deleteAlert($alert_id);
````

Contributing
------------

Contributions/PRs are welcomed. This code is MIT licensed, but pull requests are appreciated.

Other Notes
-----------

This library was built for use in http://limitless-horizons.org.
