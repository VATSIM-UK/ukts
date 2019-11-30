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

    private const NUMERIC_RATINGS = [
        0 => 'OBS',
        1 => 'S1',
        2 => 'S2',
        3 => 'S3',
        4 => 'C1',
        5 => 'C3',
    ];

    private const SUFFIX_REQUIREMENTS = [
        'DEL' => 1,
        'GND' => 1,
        'TWR' => 2,
        'APP' => 3,
        'CTR' => 4,
        'FSS' => 4,
    ];

    /**
     * Returns the code of the rating based upon the numeric key.
     *
     * @param int $index
     * @return string
     */
    public static function getRatingCodeByNumber(int $index) : string
    {
        return self::NUMERIC_RATINGS[$index];
    }

    /**
     * Get the human readable version of a given code.
     * @param string $code
     * @return string
     */
    public static function getHumanReadableRatingByCode(string $code) : string
    {
        return self::RATINGS[$code];
    }

    /**
     * Determines whether the given suffix is valid given the position.
     *
     * @param string $suffix
     * @param int $rating
     * @return bool
     */
    public static function isValidRatingForSuffix(string $suffix, int $rating)
    {
        $parsedSuffix = strtoupper($suffix);

        if (! self::isValidSuffix($parsedSuffix)) {
            return;
        }

        return $rating >= self::SUFFIX_REQUIREMENTS[$parsedSuffix];
    }

    /**
     * Determines whether the given suffix is valid for analysis.
     *
     * @param string $suffix
     * @return bool
     */
    private static function isValidSuffix(string $suffix)
    {
        return array_key_exists($suffix, self::SUFFIX_REQUIREMENTS);
    }
}
