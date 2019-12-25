<?php

use Carbon\Carbon;

class TimezoneTest extends Orchestra\Testbench\TestCase
{
	protected function getApplicationTimezone($app)
	{
		return 'America/Chicago';
	}

	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('app.timezone', 'America/Chicago');

		$mockUser = new stdClass();
		$mockUser->timezone = 'America/New_York';
		Auth::shouldReceive('user')->andreturn($mockUser);
	}

	protected function getPackageProviders($app)
	{
		return [
			\Gazugafan\Timezone\TimezoneServiceProvider::class,
		];
	}

	protected function getPackageAliases($app)
	{
		return [
			'Timezone' => 'Gazugafan\Timezone\Facades\Timezone',
		];
	}

    public function testCurrentUserTimezone()
	{
        $this->assertEquals(Timezone::getCurrentUsersTimezone(), 'America/New_York');

        Timezone::setCurrentUsersTimezoneFunction(function() {
        	return 'Test';
		});
        $this->assertEquals(Timezone::getCurrentUsersTimezone(), 'Test');

        Timezone::setCurrentUsersTimezoneFunction(null);
        $this->assertEquals(Timezone::getCurrentUsersTimezone(), 'America/New_York');
    }

    public function testCreateCarbonDates()
	{
		$carbonBase = new Carbon('2017-12-10 12:10:00', config('app.timezone'));
		$this->assertEquals(Timezone::createCarbon('2017-12-10 12:10:00', config('app.timezone')), $carbonBase);
		$this->assertEquals(Timezone::createCarbon('2017-12-10 12:10:00'), $carbonBase);
		$this->assertEquals(Timezone::createCarbon($carbonBase->timestamp), $carbonBase);
		$this->assertEquals(Timezone::createCarbon($carbonBase), $carbonBase);
		$this->assertEquals(Timezone::createCarbon($carbonBase, config('app.timezone')), $carbonBase);
		$this->assertEquals(Timezone::createCarbon(Carbon::create(2017, 12, 10, 12, 10, 0, config('app.timezone'))), $carbonBase);
		$this->assertEquals(Timezone::createCarbon('12/10/2017 12:10pm'), $carbonBase);
		$this->assertEquals(Timezone::createCarbon(Carbon::create(2017, 12, 10, 12, 10, 0))->timezoneName, 'America/Chicago');
		$this->assertEquals(Timezone::createCarbon(Carbon::create(2017, 12, 10, 12, 10, 0)), $carbonBase);
		$this->assertEquals(Timezone::createCarbon(Carbon::create(2017, 12, 10, 12, 10, 0, 'America/New_York'))->timezoneName, 'America/Chicago');
		$this->assertEquals(Timezone::createCarbon(Carbon::create(2017, 12, 10, 12, 10, 0, 'America/New_York')), $carbonBase);

		$carbonBase = new Carbon('2017-12-10 12:10:00', 'America/New_York');
		$this->assertEquals(Timezone::createCarbon('2017-12-10 12:10:00', 'America/New_York'), $carbonBase);
		$this->assertNotEquals(Timezone::createCarbon('2017-12-10 12:10:00'), $carbonBase);
    }

    public function testConvertFromStorage()
	{
        $this->assertEquals(Timezone::convertFromStorage('2017-12-10 12:10:00')->timezoneName, 'America/New_York');
        $this->assertEquals(Timezone::convertFromStorage('2017-12-10 12:10:00')->toDateTimeString(), '2017-12-10 13:10:00');
    }

    public function testConvertToStorage()
	{
        $this->assertEquals(Timezone::convertToStorage('2017-12-10 12:10:00')->timezoneName, config('app.timezone'));
        $this->assertEquals(Timezone::convertToStorage('2017-12-10 12:10:00')->toDateTimeString(), '2017-12-10 11:10:00');
    }
}
