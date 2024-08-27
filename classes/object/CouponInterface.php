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
 * Interface for managing coupons in the shopping cart enrolment plugin.
 *
 * This interface defines the methods required for handling coupons in the shopping cart.
 */
interface CouponInterface
{
    /**
     * Get the ID of a coupon based on its code.
     *
     * @param string $couponCode The coupon code.
     * @return int|null The ID of the coupon, or null if the coupon does not exist.
     */
    public static function getCouponId(string $couponCode): ?int;

    /**
     * Validate a coupon for a given cart.
     *
     * @param CartDto $cart The cart object.
     * @param int $couponId The ID of the coupon to be validated.
     * @return CouponResultDto The result of the coupon validation.
     */
    public static function validateCoupon(CartDto $cart, int $couponId): CouponResultDto;

    /**
     * Apply a coupon to a given cart.
     *
     * @param CartDto $cart The cart object.
     * @param int $couponId The ID of the coupon to be applied.
     * @return CouponResultDto The result of the coupon application.
     */
    public static function applyCoupon(CartDto $cart, int $couponId): CouponResultDto;

    /**
     * Cancel the applied coupon for a given cart.
     *
     * @param CartDto $cart The cart object.
     * @return CouponResultDto The result of the coupon cancellation.
     */
    public static function cancelCoupon(CartDto $cart): CouponResultDto;
}
