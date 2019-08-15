<?php

namespace Davidhs\Compass\Tests\Compass;

use Davidhs\Compass\Compass;
use Davidhs\Compass\Tests\TestCase;

class RoutesConfigTest extends TestCase
{
    public function test_register_app_routes()
    {
        $this->registerAppRoutes();

        $this->assertCount(12, Compass::getAppRoutes());
    }

    public function test_filter_app_routes_from_domains_rule()
    {
        $this->registerAppRoutes();

        config(['compass.routes.domains' => ['domain1.*', 'domain2.*']]);
        $this->assertCount(12, Compass::getAppRoutes());

        config(['compass.routes.domains' => ['domain1.*']]);
        $this->assertCount(6, $routes = Compass::getAppRoutes());
        foreach ($routes as $route) {
            $this->assertContains('domain1', $route['domain']);
            $this->assertNotContains('domain2', $route['domain']);
        }

        config(['compass.routes.domains' => ['domain2.*']]);
        $this->assertCount(6, $routes = Compass::getAppRoutes());
        foreach ($routes as $route) {
            $this->assertContains('domain2', $route['domain']);
            $this->assertNotContains('domain1', $route['domain']);
        }
    }

    public function test_filter_app_routes_from_prefixes_rule()
    {
        $this->registerAppRoutes();

        config(['compass.routes.prefixes' => ['prefix1/*', 'prefix2/*']]);
        $this->assertCount(8, Compass::getAppRoutes());

        config(['compass.routes.prefixes' => ['prefix1/*']]);
        $this->assertCount(4, $routes = Compass::getAppRoutes());
        foreach ($routes as $route) {
            $this->assertTrue(str_is('prefix1/*', $route['uri']));
            $this->assertFalse(str_is('prefix2/*', $route['uri']));
        }

        config(['compass.routes.prefixes' => ['prefix2/*']]);
        $this->assertCount(4, $routes = Compass::getAppRoutes());
        foreach ($routes as $route) {
            $this->assertTrue(str_is('prefix2/*', $route['uri']));
            $this->assertFalse(str_is('prefix1/*', $route['uri']));
        }
    }

    public function test_filter_app_routes_from_exclude_rule()
    {
        $this->registerAppRoutes();

        config(['compass.routes.exclude' => ['*']]);
        $this->assertCount(0, Compass::getAppRoutes());

        config(['compass.routes.exclude' => ['compass.*', 'prefix1.domain1-1']]);
        $this->assertCount(11, $routes = Compass::getAppRoutes());
        foreach ($routes as $route) {
            $this->assertFalse(str_is('compass.*', $route['domain']));
            $this->assertNotContains('prefix.domain1-1', $route['name']);
        }

        config(['compass.routes.exclude' => ['compass.*', 'prefix1.domain1-*']]);
        $this->assertCount(10, $routes = Compass::getAppRoutes());
        foreach ($routes as $route) {
            $this->assertFalse(str_is('compass.*', $route['domain']));
            $this->assertFalse(str_is('prefix.domain1-*', $route['name']));
        }
    }
}
