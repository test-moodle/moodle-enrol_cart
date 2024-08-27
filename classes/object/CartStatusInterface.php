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
 * Interface CartStatus
 * Defines constants for cart status.
 */
interface CartStatusInterface
{
    /** @var int The current status of the cart */
    public const STATUS_CURRENT = 0;
    /** @var int The status when user is in the process of checkout */
    public const STATUS_CHECKOUT = 10;
    /** @var int The status when the cart has been canceled by the user */
    public const STATUS_CANCELED = 70;
    /** @var int The status when items in the cart have been delivered to the user */
    public const STATUS_DELIVERED = 90;
}
