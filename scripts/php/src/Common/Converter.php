<?php

    namespace Common;

    class Converter
    {

        /**
         * Convert number to string represented only by lower latin alphabet except u sign. u will be used as delimiter
         *
         * @param int $number - number to convert
         *
         * @return string - string representation of provided number
         */
        public static function numberToString(int $number)
        {

            $converted = base_convert($number, 10, 25);
            $converted = strtr($converted,
                array(
                    0 => 'x',
                    1 => 'q',
                    2 => 'y',
                    3 => 't',
                    4 => 'p',
                    5 => 'v',
                    6 => 's',
                    7 => 'w',
                    8 => 'z',
                    9 => 'r',
                ));

            return $converted;
        }

        /**
         * Reverse process to numberToString
         *
         * @param string $encoded_number
         *
         * @return int
         */
        public static function stringToNumber(string $encoded_number)
        {

            $number = strtr($encoded_number,
                array(
                    'x' => 0,
                    'q' => 1,
                    'y' => 2,
                    't' => 3,
                    'p' => 4,
                    'v' => 5,
                    's' => 6,
                    'w' => 7,
                    'z' => 8,
                    'r' => 9
                ));
            $number = (int)base_convert($number, 25, 10);

            return $number;

        }

    }