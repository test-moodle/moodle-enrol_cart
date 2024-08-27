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
 * Data Transfer Object for Cart Item.
 *
 * This class is used to transfer data related to a cart item in the enrolment system.
 */
class CartItemDto
{
    private ?CartItem $cartItem;

    /**
     * Constructor for CartItemDto.
     *
     * @param CartItem $cartItem The cart item object to be used for data transfer.
     */
    public function __construct(CartItem $cartItem)
    {
        $this->cartItem = $cartItem;
    }

    /**
     * Get the ID of the cart item.
     *
     * @return int The ID of the cart item.
     */
    public function getItemId(): int
    {
        return $this->cartItem->id;
    }

    /**
     * Get the ID of the course associated with the cart item.
     *
     * @return int The ID of the course.
     */
    public function getCourseId(): int
    {
        return $this->cartItem->course->id;
    }

    /**
     * Get the price of the cart item.
     *
     * @return float The price of the cart item.
     */
    public function getPrice(): float
    {
        return $this->cartItem->price;
    }

    /**
     * Get the payable amount for the cart item after applying any discounts.
     *
     * @return float The payable amount for the cart item.
     */
    public function getPayable(): float
    {
        return $this->cartItem->payable;
    }

    /**
     * Check if the cart item has any discount applied.
     *
     * @return bool True if a discount is applied, false otherwise.
     */
    public function hasDiscount(): bool
    {
        return $this->cartItem->hasDiscount;
    }
}
