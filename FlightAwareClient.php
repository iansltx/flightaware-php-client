<?php

class FlightAwareClient
{
    protected $username;
    protected $apiKey;

    protected static $baseUrl = 'http://flightxml.flightaware.com/json/FlightXML2/';

    public function __construct($username, $api_key) {
        $this->username = $username;
        $this->apiKey = $api_key;
    }

    public function inFlightInfo($ident) {
        $result = $this->get('InFlightInfo', array('ident' => $ident))->InFlightInfoResult;

        if (!strlen($result->faFlightID)) {
            throw new RuntimeException('No live or recent flight info was found', 404);
        }

        $waypoints = array();
        $currentWaypoint = array();
        foreach (explode(' ', $result->waypoints) as $coordinate) {
            if (isset($currentWaypoint['latitude'])) {
                $currentWaypoint['longitude'] = $coordinate;
                $waypoints[] = $currentWaypoint;
                $currentWaypoint = array();
            } else {
                $currentWaypoint['latitude'] = $coordinate;
            }
        }
        $result->waypoints = $waypoints;

        return $result;
    }

    public function flightInfoEx($ident, $how_many = 1, $offset = 0) {
    	return $this->get('FlightInfoEx', array('ident' => $ident, 'howMany' => $how_many, 'offset' => $offset))
    		->FlightInfoExResult->flights;
    }

    public function getFlightId($ident, $departure_time) {
    	if ($departure_time instanceof DateTime) {
    		$departure_time = $departure_time->getTimestamp();
    	} else if (!is_numeric($departure_time)) {
    		$departure_time = strtotime($departure_time);
    	}

    	return $this->get('GetFlightID', array('ident' => $ident, 'departureTime' => $departure_time))->GetFlightIDResult;
    }

    public function getHistoricalTrack($flight_id) {
    	return $this->get('GetHistoricalTrack', array('faFlightID' => $flight_id))->GetHistoricalTrackResult->data;
    }

    public function getLastTrack($ident) {
        return $this->get('GetLastTrack', array('ident' => $ident))->GetLastTrackResult->data;
    }

    /** wraps cURL for returning FlightXML v2 JSON data, JSON-decoded **/
    protected function get($endpoint, $params) {
        $ch = curl_init(self::$baseUrl . $endpoint . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FlightAware REST PHP Library 0.1');

        $output = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseCode === 401) {
            throw new RuntimeException('Authentication failed; use your FlightAware username and API key.', 401);
        } else if ($responseCode !== 200) {
            throw new RuntimeException($output, $responseCode);
        }

        curl_close($ch);

        $result = json_decode($output);

        if (isset($result->error)) {
    		if ($result->error === 'no data available') {
    			throw new InvalidArgumentException('no data available', 404);
    		}

    		throw new RuntimeException($result->error, 400);
    	}

    	return $result;
    }
}
