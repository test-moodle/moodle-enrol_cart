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

use core\notification;
use core_payment\helper;
use enrol_cart\helper\CartHelper;
use enrol_cart\helper\PaymentHelper;
use enrol_cart\object\Cart;

require_once '../../config.php';

global $PAGE, $OUTPUT, $CFG, $USER;

// Retrieve parameters from the request
$cartId = optional_param('id', null, PARAM_ALPHANUM);
$gateway = optional_param('gateway', null, PARAM_ALPHANUM);
$couponCode = optional_param('coupon_code', null, PARAM_ALPHANUM);

// Ensure the user is logged in and has a valid session
require_login();
require_sesskey();

// Set up the page context and layout
$title = get_string('pluginname', 'enrol_cart') . ' - ' . get_string('payment', 'enrol_cart');
$url = new moodle_url('/enrol/cart/payment.php');
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagetype('cart');
$PAGE->set_url($url);

// Add navigation nodes
$node1 = $PAGE->navigation->add(
    get_string('my_purchases', 'enrol_cart'),
    new moodle_url('/enrol/cart/my.php'),
    navigation_node::TYPE_CONTAINER,
);
$node2 = $node1->add(
    get_string('pluginname', 'enrol_cart'),
    CartHelper::getCartViewUrl($cartId),
    navigation_node::TYPE_CONTAINER,
);
$node2->add(get_string('payment', 'enrol_cart'), $url)->make_active();

// Get the cart object
$cart = $cartId ? Cart::findOne($cartId) : CartHelper::getCurrent();

// Check if the cart is payable
if (
    !$cart ||
    $cart->isEmpty ||
    !($cart->isCheckout || $cart->isCurrent) ||
    !$cart->isCurrentUserOwner ||
    $cart->isDelivered
) {
    redirect(CartHelper::getCartViewUrl());
    exit();
}

// Prepare and validate payment gateway
$gateway = $gateway ?: PaymentHelper::getRandPaymentGateway();
if (!PaymentHelper::isPaymentGatewayValid($gateway)) {
    notification::error(get_string('error_gateway_is_invalid', 'enrol_cart'));
    redirect($cart->checkoutUrl);
    exit();
}

// Refresh the cart to update item prices and totals
$cart->refresh();

// Validate the coupon if any is applied
if (!$cart->couponCheckAvailability() && !$cart->couponCancel()) {
    notification::warning($cart->couponErrorMessage ?: get_string('error_coupon_is_invalid', 'enrol_cart'));
    redirect($cart->checkoutUrl);
    exit();
}

// Check if the cart has changed during the process
if ($cart->hasChanged) {
    notification::warning(get_string('msg_cart_changed', 'enrol_cart'));
    redirect($cart->checkoutUrl);
    exit();
}

// Apply the coupon code if provided
if (!$cart->coupon_id && $couponCode && !$cart->couponApply($couponCode)) {
    notification::error($cart->couponErrorMessage ?: get_string('error_coupon_apply_failed', 'enrol_cart'));
    redirect($cart->checkoutUrl);
    exit();
}

// Process the cart if the final payable amount is zero
if ($cart->isFinalPayableZero) {
    $cart->processFreeItems();
    redirect($cart->viewUrl);
    exit();
}

// Proceed to checkout
$cart->checkout();

// Prepare payment method details
$component = 'enrol_cart';
$paymentArea = 'cart';
$itemId = $cart->id;
$description = '';
$successUrl = helper::get_success_url($component, $paymentArea, $itemId)->out(false);

// Load payment JavaScript module
$PAGE->requires->js_call_amd('enrol_cart/payment', 'init', [
    $gateway,
    $component,
    $paymentArea,
    $itemId,
    $successUrl,
    $description,
]);

// Render the payment page
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('enrol_cart/payment', [
    'cart' => $cart,
    'gateway' => $gateway,
    'component' => $component,
    'payment_area' => $paymentArea,
    'item_id' => $itemId,
    'success_url' => $successUrl,
    'description' => $description,
]);
echo $OUTPUT->footer();
