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

namespace enrol_cart\helper;

defined('MOODLE_INTERNAL') || die();

use enrol_cart\object\CartDto;
use enrol_cart\object\CouponInterface;
use enrol_cart\object\CouponResultDto;

/**
 * Class CouponHelper
 * @brief Provides helper functions to manage coupon-related functionalities in the shopping cart.
 */
class CouponHelper
{
    /**
     * Retrieves the name of the coupon class from the configuration.
     *
     * @return string|null The name of the coupon class, or null if not defined.
     */
    public static function getCouponClassName(): ?string
    {
        return (string) CartHelper::getConfig('coupon_class') ?: null;
    }

    /**
     * Checks if the coupon class is defined and implements the CouponInterface.
     *
     * @return bool True if the coupon class is defined and valid, false otherwise.
     */
    public static function existsCouponClass(): bool
    {
        $couponClass = self::getCouponClassName();

        return $couponClass &&
            class_exists($couponClass) &&
            in_array('enrol_cart\object\CouponInterface', class_implements($couponClass));
    }

    /**
     * Checks if the coupon functionality is enabled in the configuration.
     *
     * @return bool True if the coupon functionality is enabled, false otherwise.
     */
    public static function isCouponEnable(): bool
    {
        return CartHelper::getConfig('coupon_enable') && static::existsCouponClass();
    }

    /**
     * Retrieves the ID of a coupon based on its code.
     *
     * @param string $couponCode The code of the coupon.
     * @return int|null The ID of the coupon, or null if not found or disabled.
     */
    public static function getCouponId(string $couponCode): ?int
    {
        if (!self::isCouponEnable()) {
            return null;
        }

        /** @var CouponInterface $couponClassName */
        $couponClassName = CouponHelper::getCouponClassName();

        return $couponClassName::getCouponId($couponCode);
    }

    /**
     * Validates a coupon for a given cart.
     *
     * @param CartDto $cart The cart to validate the coupon for.
     * @param int $couponId The ID of the coupon to validate.
     * @return CouponResultDto The result of the coupon validation.
     */
    public static function couponValidate(CartDto $cart, int $couponId): CouponResultDto
    {
        if (!self::isCouponEnable()) {
            return new CouponResultDto();
        }

        /** @var CouponInterface $couponClassName */
        $couponClassName = CouponHelper::getCouponClassName();

        return $couponClassName::validateCoupon($cart, $couponId);
    }

    /**
     * Applies a coupon to a given cart.
     *
     * @param CartDto $cart The cart to apply the coupon to.
     * @param int $couponId The ID of the coupon to apply.
     * @return CouponResultDto The result of the coupon application.
     */
    public static function couponApply(CartDto $cart, int $couponId): CouponResultDto
    {
        if (!static::isCouponEnable()) {
            return new CouponResultDto();
        }

        /** @var CouponInterface $couponClassName */
        $couponClassName = CouponHelper::getCouponClassName();

        return $couponClassName::applyCoupon($cart, $couponId);
    }

    /**
     * Cancels the usage of a coupon for a given cart.
     *
     * @param CartDto $cart The cart to cancel the coupon for.
     * @return CouponResultDto The result of the coupon cancellation.
     */
    public static function couponCancel(CartDto $cart): CouponResultDto
    {
        if (!static::isCouponEnable()) {
            return new CouponResultDto();
        }

        /** @var CouponInterface $couponClassName */
        $couponClassName = CouponHelper::getCouponClassName();

        return $couponClassName::cancelCoupon($cart);
    }
}
