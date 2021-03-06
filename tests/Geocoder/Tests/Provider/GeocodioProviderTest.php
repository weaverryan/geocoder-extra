<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeocodioProvider;

class GeocodioProviderTest extends TestCase
{
    const MISSING_API_KEY = 'You need to configure the GEOCODIO_API_KEY value in phpunit.xml';

    public function testGetName()
    {
        $provider = new GeocodioProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('geocodio', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for given query: http://api.geocod.io/v1/geocode?q=foobar&api_key=9999
     */
    public function testGetGeocodedData()
    {
        $provider = new GeocodioProvider($this->getMockAdapter(), '9999');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for given query: http://api.geocod.io/v1/geocode?q=1+Infinite+Loop+Cupertino%2C+CA+95014&api_key=9999
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new GeocodioProvider($this->getMockAdapterReturns(null), '9999');
        $provider->geocode('1 Infinite Loop Cupertino, CA 95014');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid API Key
     */
    public function testGetGeocodedDataWithBadAPIKeyThrowsException()
    {
        $provider = new GeocodioProvider($this->getAdapter(), '9999');
        $results  = $provider->geocode('1 Infinite Loop Cupertino, CA 95014');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $api_key = $this->getApiKey('GEOCODIO_API_KEY');

        if ($api_key === false) {
            $this->markTestSkipped(self::MISSING_API_KEY);
        }

        $provider = new GeocodioProvider($this->getAdapter(), $api_key);
        $results  = $provider->geocode('1 Infinite Loop Cupertino, CA 95014');

        $this->assertInternalType('array', $results);

        $result = $results[0];
        $this->assertEquals(37.331551291667, $result['latitude'], '', 0.01);
        $this->assertEquals(-122.03057125, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertEquals('1', $result['streetNumber']);
        $this->assertEquals('Infinite Loop', $result['streetName']);
        $this->assertEquals(95014, $result['zipcode']);
        $this->assertEquals('Cupertino', $result['city']);
        $this->assertEquals('Santa Clara County', $result['county']);
        $this->assertEquals('CA', $result['region']);
        $this->assertEquals('US', $result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressThatDoesNotReturnStreet()
    {
        $api_key = $this->getApiKey('GEOCODIO_API_KEY');

        if ($api_key === false) {
            $this->markTestSkipped(self::MISSING_API_KEY);
        }

        $provider = new GeocodioProvider($this->getAdapter(), $api_key);
        //Geocodio currently incorrectly parses this address, but Geocodio does know of the bug
        $results  = $provider->getGeocodedData('386 Branam Rd Old Fort TN 37362');

        $this->assertInternalType('array', $results);

        $result = $results[0];
        $this->assertEquals(35.049196999999999, $result['latitude'], '', 0.01);
        $this->assertEquals(-84.735365999999999, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertEquals('386', $result['streetNumber']);
        $this->assertEquals('Branam Rd Old Ft', $result['streetName']); // Testing bad parsing
        $this->assertEquals(37362, $result['zipcode']);
        $this->assertEquals('Oldfort', $result['city']);
        $this->assertNull($result['county']);
        $this->assertEquals('TN', $result['region']);
        $this->assertEquals('US', $result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressWithoutStreet()
    {
        $api_key = $this->getApiKey('GEOCODIO_API_KEY');

        if ($api_key === false) {
            $this->markTestSkipped(self::MISSING_API_KEY);
        }

        $provider = new GeocodioProvider($this->getAdapter(), $api_key);
        $results  = $provider->getGeocodedData('Old Fort TN 37362');

        $this->assertInternalType('array', $results);

        $result = $results[0];
        $this->assertEquals(35.049196999999999, $result['latitude'], '', 0.01);
        $this->assertEquals(-84.735365999999999, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertEquals('', $result['streetNumber']);
        $this->assertEquals('', $result['streetName']);
        $this->assertEquals(37362, $result['zipcode']);
        $this->assertEquals('Oldfort', $result['city']);
        $this->assertNull($result['county']);
        $this->assertEquals('TN', $result['region']);
        $this->assertEquals('US', $result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     */
    public function testGetReversedData()
    {
        $api_key = $this->getApiKey('GEOCODIO_API_KEY');

        if ($api_key === false) {
            $this->markTestSkipped(self::MISSING_API_KEY);
        }

        $provider = new GeocodioProvider($this->getMockAdapter(), $api_key);
        $provider->reverse(1, 2);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $api_key = $this->getApiKey('GEOCODIO_API_KEY');

        if ($api_key === false) {
            $this->markTestSkipped(self::MISSING_API_KEY);
        }

        $provider = new GeocodioProvider($this->getAdapter(), $api_key);
        $result   = $provider->reverse(37.331551291667, -122.03057125);

        $this->assertInternalType('array', $result);

        $result = $result[0];
        $this->assertEquals(37.331551291667, $result['latitude'], '', 0.01);
        $this->assertEquals(-122.03057125, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertEquals('Infinite Loop', $result['streetName']);
        $this->assertEquals(95014, $result['zipcode']);
        $this->assertEquals('Cupertino', $result['city']);
        $this->assertEquals('Santa Clara County', $result['county']);
        $this->assertEquals('CA', $result['region']);
        $this->assertEquals('US', $result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    protected function getApiKey($key = null)
    {
        return (!empty($key) && isset($_SERVER[$key])) ? $_SERVER[$key] : false;
    }
}
