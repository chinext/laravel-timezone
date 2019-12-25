<?php namespace Chinext\Timezone;

use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Timezone
{
	public $getCurrentUsersTimezoneFunction = null;

	/**
	 * Sets a function to be used to get the current user's timezone--overriding the default behavior of Auth::user()->timezone
	 *
	 * @param $newFunction A new function to call that returns the currently logged in user's timezone. Don't specify or set to NULL to reset to default behavior.
	 */
	public function setCurrentUsersTimezoneFunction($newFunction = null)
	{
		$this->getCurrentUsersTimezoneFunction = $newFunction;
	}

	/**
	 * Gets the currently logged in user's timezone as a string, or the application timezone if no user is logged in.
	 *
	 * @return string The current user's timezone
	 */
	public function getCurrentUsersTimezone()
	{
		if ($this->getCurrentUsersTimezoneFunction && is_callable($this->getCurrentUsersTimezoneFunction))
			return $this->getCurrentUsersTimezoneFunction->__invoke();

		if ($user = Auth::user())
		{
			if (isset($user->timezone))
				return $user->timezone;
		}

		return config('app.timezone');
	}

	/**
	 * Creates a Carbon date from a variety of date representations. If a Carbon or DateTime object containing a timezone is passed along with the $timezone parameter, the returned Carbon will be in the $timezone timezone without any adjustments.
	 *
	 * @param Carbon|DateTime|string|int|null $date The date to create a Carbon from. Can be essentially anything that can be interpreted as a date, including DateTime, string, timestamp, another Carbon instance, etc. If null, now is assumed.
	 * @param string $timezone The timezone to create the Carbon instance in. If not specified, the application timezone is assumed.
	 *
	 * @return Carbon The interpreted date as a Carbon instance
	 */
	public function createCarbon($date = null, $timezone = null)
	{
		if (!$timezone) $timezone = config('app.timezone');
		if (is_integer($date)) $date = date('Y-m-d H:i:s', $date);
		return new Carbon($date, $timezone);
	}

	/**
	 * Converts a date from storage into a Carbon date adjusted to the user's timezone.
	 *
	 * @param Carbon|DateTime|string|int $date The date to convert (or now, if not specified). Can be a Carbon, DateTime, string, timestamp, etc.
	 * @param string $toTimezone If specified, the date will be converted to this timezone. Otherwise, the current user's timezone is assumed.
	 *
	 * @return Carbon The converted date
     */
	public function convertFromStorage($date = null, $toTimezone = null)
	{
		if (!$toTimezone) $toTimezone = $this->getCurrentUsersTimezone();
		$carbonDate = $this->createCarbon($date);

		$carbonDate->timezone = $toTimezone;

		return $carbonDate;
    }

	/**
	 * Converts a date from the user's timezone into a Carbon date adjusted for the storage timezone.
	 *
	 * @param Carbon|DateTime|string|int $date The date to convert (or now, if not specified). Can be a Carbon, DateTime, string, timestamp, etc.
	 * @param string $fromTimezone If specified, the date will be converted from this timezone. Otherwise, the current user's timezone is assumed.
	 *
	 * @return Carbon The converted date
     */
	public function convertToStorage($date = null, $fromTimezone = null)
	{
		if (!$fromTimezone) $fromTimezone = $this->getCurrentUsersTimezone();
		$carbonDate = $this->createCarbon($date, $fromTimezone);

		$carbonDate->timezone = config('app.timezone');

		return $carbonDate;
    }
}