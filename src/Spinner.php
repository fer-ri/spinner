<?php

namespace Ferri;

class Spinner
{
    /**
     * Auto detect text contain nested or flat syntax.
     *
     * @param  string
     * @param  bool
     * @param  string
     * @param  string
     * @param  string
     * @return string
     */
    public static function detect($text, $seedPageName = false, $openingConstruct = '{', $closingConstruct = '}', $separator = '|')
    {
        if (preg_match('~'.$openingConstruct.'(?:(?!'.$closingConstruct.').)*'.$openingConstruct.'~s', $text)) {
            return self::nested($text, $seedPageName, $openingConstruct, $closingConstruct, $separator);
        } else {
            return self::flat($text, $seedPageName, false, $openingConstruct, $closingConstruct, $separator);
        }
    }

    /**
     * Spin text without nested syntax.
     *
     * @param  string
     * @param  bool
     * @param  bool
     * @param  string
     * @param  string
     * @param  string
     * @return string
     */
    public static function flat($text, $seedPageName = false, $calculate = false, $openingConstruct = '{', $closingConstruct = '}', $separator = '|')
    {
        // Choose whether to return the string or the number of permutations
        $return = 'text';

        if ($calculate) {
            $permutations = 1;
            $return = 'permutations';
        }

        // If we have nothing to spin just exit (don't use a regexp)
        if (strpos($text, $openingConstruct) === false) {
            return $$return;
        }

        if (preg_match_all('!'.$openingConstruct.'(.*?)'.$closingConstruct.'!s', $text, $matches)) {
            // Optional, always show a particular combination on the page
            self::checkSeed($seedPageName);

            $find = [];
            $replace = [];

            foreach ($matches[0] as $key => $match) {
                $choices = explode($separator, $matches[1][$key]);

                if ($calculate) {
                    $permutations *= count($choices);
                } else {
                    $find[] = $match;
                    $replace[] = $choices[mt_rand(0, count($choices) - 1)];
                }
            }

            if (! $calculate) {
                // Ensure multiple instances of the same spinning combinations will spin differently
                $text = self::str_replace_first($find, $replace, $text);
            }
        }

        return $$return;
    }

    /**
     * Spin text with nested syntax.
     *
     * @param  string
     * @param  bool
     * @param  string
     * @param  string
     * @param  string
     * @return string
     */
    public static function nested($text, $seedPageName = false, $openingConstruct = '{', $closingConstruct = '}', $separator = '|')
    {
        // If we have nothing to spin just exit (don't use a regexp)
        if (strpos($text, $openingConstruct) === false) {
            return $text;
        }

        // Find the first whole match
        if (preg_match('!'.$openingConstruct.'(.+?)'.$closingConstruct.'!s', $text, $matches)) {
            // Optional, always show a particular combination on the page
            self::checkSeed($seedPageName);

            // Only take the last block
            if (($pos = mb_strrpos($matches[1], $openingConstruct)) !== false) {
                $matches[1] = mb_substr($matches[1], $pos + mb_strlen($openingConstruct));
            }

            // And spin it
            $parts = explode($separator, $matches[1]);
            $text = self::str_replace_first($openingConstruct.$matches[1].$closingConstruct, $parts[mt_rand(0, count($parts) - 1)], $text);

            // We need to continue until there is nothing left to spin
            return self::nested($text, $seedPageName, $openingConstruct, $closingConstruct, $separator);
        } else {
            // If we have nothing to spin just exit
            return $text;
        }
    }

    /**
     * Similar to str_replace, but only replaces the first instance of the needle.
     *
     * @param  string
     * @param  string
     * @param  string
     * @return string
     */
    private static function str_replace_first($find, $replace, $string)
    {
        // Ensure we are dealing with arrays
        if (! is_array($find)) {
            $find = [$find];
        }

        if (! is_array($replace)) {
            $replace = [$replace];
        }

        foreach ($find as $key => $value) {
            if (($pos = mb_strpos($string, $value)) !== false) {
                // If we have no replacement make it empty
                if (! isset($replace[$key])) {
                    $replace[$key] = '';
                }

                $string = mb_substr($string, 0, $pos).$replace[$key].mb_substr($string, $pos + mb_strlen($value));
            }
        }

        return $string;
    }

    /**
     *  Check seed for tokenized spinned text.
     * @param  [type]
     * @return null
     * @throws \Exception
     */
    private static function checkSeed($seedPageName)
    {
        $signature = (php_sapi_name() === 'cli' or defined('STDIN'))
         ? $_SERVER['SCRIPT_NAME']
         : $_SERVER['REQUEST_URI'];

        // Don't do the check if we are using random seeds
        if ($seedPageName) {
            if ($seedPageName === true) {
                mt_srand(crc32($signature));
            } elseif ($seedPageName == 'every second') {
                mt_srand(crc32($signature.date('Y-m-d-H-i-s')));
            } elseif ($seedPageName == 'every minute') {
                mt_srand(crc32($signature.date('Y-m-d-H-i')));
            } elseif ($seedPageName == 'hourly' or $seedPageName == 'every hour') {
                mt_srand(crc32($signature.date('Y-m-d-H')));
            } elseif ($seedPageName == 'daily' or $seedPageName == 'every day') {
                mt_srand(crc32($signature.date('Y-m-d')));
            } elseif ($seedPageName == 'weekly' or $seedPageName == 'every week') {
                mt_srand(crc32($signature.date('Y-W')));
            } elseif ($seedPageName == 'monthly' or $seedPageName == 'every month') {
                mt_srand(crc32($signature.date('Y-m')));
            } elseif ($seedPageName == 'annually' or $seedPageName == 'every year') {
                mt_srand(crc32($signature.date('Y')));
            } elseif (preg_match('!every ([0-9.]+) seconds!', $seedPageName, $matches)) {
                mt_srand(crc32($signature.floor(time() / $matches[1])));
            } elseif (preg_match('!every ([0-9.]+) minutes!', $seedPageName, $matches)) {
                mt_srand(crc32($signature.floor(time() / ($matches[1] * 60))));
            } elseif (preg_match('!every ([0-9.]+) hours!', $seedPageName, $matches)) {
                mt_srand(crc32($signature.floor(time() / ($matches[1] * 3600))));
            } elseif (preg_match('!every ([0-9.]+) days!', $seedPageName, $matches)) {
                mt_srand(crc32($signature.floor(time() / ($matches[1] * 86400))));
            } elseif (preg_match('!every ([0-9.]+) weeks!', $seedPageName, $matches)) {
                mt_srand(crc32($signature.floor(time() / ($matches[1] * 604800))));
            } elseif (preg_match('!every ([0-9.]+) months!', $seedPageName, $matches)) {
                mt_srand(crc32($signature.floor(time() / ($matches[1] * 2620800))));
            } elseif (preg_match('!every ([0-9.]+) years!', $seedPageName, $matches)) {
                mt_srand(crc32($signature.floor(time() / ($matches[1] * 31449600))));
            } else {
                throw new Exception($seedPageName.' Was not a valid spin time option!');
            }
        }
    }
}
