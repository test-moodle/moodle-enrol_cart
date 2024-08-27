<?php

/**
 * @package    enrol_cart
 * @brief      Shopping Cart Enrolment Plugin for Moodle
 * @category   Moodle, Enrolment, Shopping Cart
 *
 * @author     MohammadReza PourMohammad <onbirdev@gmail.com>
 * @copyright  2024 MohammadReza PourMohammad
 * @link       https://onbir.dev
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_cart\formatter;

use core_payment\helper;
use enrol_cart\helper\CartHelper;

defined('MOODLE_INTERNAL') || die();

/**
 * Class CurrencyHelper
 * Provides utility functions for managing payment currency.
 */
class CurrencyFormatter
{
    /**
     * Get the mapping of currency codes to human-readable names.
     *
     * @return array An associative array mapping currency codes to human-readable names.
     */
    protected static function getCurrencyNameConvert(): array
    {
        return [
            'IRR' => get_string('IRR', 'enrol_cart'),
            'IRT' => get_string('IRT', 'enrol_cart'),
        ];
    }

    /**
     * Convert English numbers in a given text to Farsi numbers.
     *
     * @param string $text The text containing English numbers.
     * @return string The text with English numbers converted to Farsi.
     */
    public static function convertEnglishNumbersToPersian(string $text): string
    {
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '%'];
        $farsiNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٪'];

        return str_replace($englishNumbers, $farsiNumbers, $text);
    }

    /**
     * Returns a human-readable amount with the correct number of fractional digits and currency indicator,
     * and can also apply a surcharge or convert IRR to IRT based on configuration.
     *
     * @param float $amount Amount in the currency units.
     * @param string $currency The currency code.
     * @return string The formatted cost string.
     */
    public static function getCostAsFormatted(float $amount, string $currency): string
    {
        // Convert IRR to IRT if configured
        if ($currency == 'IRR' && CartHelper::getConfig('convert_irr_to_irt')) {
            $amount = $amount / 10;
        }
        $cost = helper::get_cost_as_string($amount, $currency);

        // Replace IRR with IRT in the cost string if configured
        if ($currency == 'IRR' && CartHelper::getConfig('convert_irr_to_irt')) {
            $cost = str_replace('IRR', 'IRT', $cost);
        }

        // Convert currency codes to human-readable format
        foreach (self::getCurrencyNameConvert() as $item => $value) {
            $cost = str_replace($item, '<span>' . $value . '</span>', $cost);
        }

        // Convert numbers to Persian if configured
        if (CartHelper::getConfig('convert_numbers_to_persian')) {
            $cost = self::convertEnglishNumbersToPersian($cost);
        }

        return $cost;
    }
}
