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

namespace enrol_cart\object;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface DiscountType
 * Defines constants for discount type.
 */
interface DiscountTypeInterface
{
    public const NO_DISCOUNT = 0;
    public const PERCENTAGE = 10;
    public const FIXED = 20;
}
