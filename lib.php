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

use enrol_cart\helper\CartHelper;

defined('MOODLE_INTERNAL') || die();

function enrol_cart_render_navbar_output(renderer_base $renderer)
{
    if (!enrol_is_enabled('cart')) {
        return '';
    }

    $cart = CartHelper::getCurrent();
    return $renderer->render_from_template('enrol_cart/cart_button', [
        'count' => $cart ? $cart->count : 0,
        'view_url' => CartHelper::getCartViewUrl(),
    ]);
}
