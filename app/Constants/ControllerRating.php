<?php

namespace App\Constants;

final class ControllerRating
{
    private const RATINGS = [
        'OBS' => 'Observer',
        'S1' => 'Student 1',
        'S2' => 'Student 2',
        'S3' => 'Senior Student',
        'C1' => 'Controller 1',
        'C3' => 'Controller 3',
    ];

    /**
     * Indexes reference the VATSIM data representation of the ratings.
     */
    private const NUMERIC_RATINGS = [
        1 => 'OBS',
        2 => 'S1',
        3 => 'S2',
        4 => 'S3',
        5 => 'C1',
        6 => 'C3',
    ];

    private const SUFFIX_REQUIREMENTS = [
        'DEL' => 2,
        'GND' => 2,
        'TWR' => 3,
        'APP' => 4,
        'CTR' => 5,
        'FSS' => 5,
    ];

    /**
     * Returns the code of the rating based upon the numeric key.
     *
     * @param  int  $index
     * @return string
     */
    public static function getRatingCodeByNumber(int $index): ?string
    {
        return self::NUMERIC_RATINGS[$index] ?? null;
    }

    /**
     * Get the human readable version of a given code.
     * @param  string  $code
     * @return string
     */
    public static function getHumanReadableRatingByCode(string $code): string
    {
        return self::RATINGS[$code];
    }

    /**
     * Determines whether the given suffix is valid given the position.
     *
     * @param  string  $suffix
     * @param  int  $rating
     * @return bool
     */
    public static function isValidRatingForSuffix(string $suffix, int $rating)
    {
        $parsedSuffix = strtoupper($suffix);

        if (! self::isValidSuffix($parsedSuffix)) {
            return false;
        }

        return $rating >= self::SUFFIX_REQUIREMENTS[$parsedSuffix];
    }

    /**
     * Determines whether the given suffix is valid for analysis.
     *
     * @param  string  $suffix
     * @return bool
     */
    private static function isValidSuffix(string $suffix)
    {
        return array_key_exists($suffix, self::SUFFIX_REQUIREMENTS);
    }
}
