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

use coding_exception;

/**
 * Trait providing methods related to coupon discount types.
 * @property int $discount_type
 * @property string $discountTypeName
 * @property bool $isDiscountTypePercentage
 * @property bool $isDiscountTypeFixed
 */
trait DiscountTypeTrait
{
    /**
     * Retrieve a list of available coupon discount type options.
     *
     * @return array An array of discount type options.
     */
    public static function getDiscountTypeOptions(): array
    {
        return [
            DiscountTypeInterface::NO_DISCOUNT => get_string('no_discount', 'enrol_cart'),
            DiscountTypeInterface::PERCENTAGE => get_string('percentage', 'enrol_cart'),
            DiscountTypeInterface::FIXED => get_string('fixed', 'enrol_cart'),
        ];
    }

    /**
     * Retrieve the name of the discount type.
     *
     * @return string The name of the discount type.
     * @throws coding_exception If the discount type is unknown.
     */
    public function getDiscountTypeName(): string
    {
        $options = static::getDiscountTypeOptions();
        return $options[$this->discount_type] ?? get_string('unknown', 'enrol_cart');
    }

    /**
     * Check if the discount type is no discount.
     *
     * @return bool True if the discount type is no discount, false otherwise.
     */
    public function getIsDiscountTypeNoDiscount(): bool
    {
        return DiscountTypeInterface::NO_DISCOUNT == $this->discount_type;
    }

    /**
     * Check if the discount type is percentage.
     *
     * @return bool True if the discount type is percentage, false otherwise.
     */
    public function getIsDiscountTypePercentage(): bool
    {
        return DiscountTypeInterface::PERCENTAGE == $this->discount_type;
    }

    /**
     * Check if the discount type is fixed.
     *
     * @return bool True if the discount type is fixed, false otherwise.
     */
    public function getIsDiscountTypeFixed(): bool
    {
        return DiscountTypeInterface::FIXED == $this->discount_type;
    }
}
