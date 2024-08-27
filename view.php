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
use enrol_cart\object\Cart;

require_once '../../config.php';

global $PAGE, $OUTPUT, $CFG, $USER;

// Retrieve the cart ID from the request
$id = optional_param('id', null, PARAM_INT);

// Require login if cart ID is provided
if ($id) {
    require_login();
}

// Set up the page context and layout
$title = get_string($id ? 'order' : 'pluginname', 'enrol_cart');
$url = CartHelper::getCartViewUrl($id);
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagetype('cart');
$PAGE->set_url($url);

// Retrieve the cart object
$cart = $id ? Cart::findOne($id) : CartHelper::getCurrent();

// Check if the current user can manage the cart
$canManage = has_capability('enrol/cart:manage', $context);

// Add navigation nodes
if ($cart && $cart->isCurrentUserOwner) {
    $node1 = $PAGE->navigation->add(
        get_string('my_purchases', 'enrol_cart'),
        new moodle_url('/enrol/cart/my.php'),
        navigation_node::TYPE_CONTAINER,
    );
    $node1->add($title, $url)->make_active();
} else {
    $PAGE->navigation->add($title, $url, navigation_node::TYPE_CONTAINER);
}

// Render an empty cart view if the cart is empty or invalid
if (!$cart || $cart->isEmpty) {
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('enrol_cart/view_empty', []);
    echo $OUTPUT->footer();
    exit();
}

// Ensure the current user owns the cart or has management capability
if (!$cart->isCurrentUserOwner && !$canManage) {
    print_error('error_invalid_cart', 'enrol_cart');
}

// Refresh the cart to validate and update item prices
$cart->refresh();

// Render a cart view based on the user's capabilities and cart state
if (!$cart->isCurrent || (!$cart->isCurrentUserOwner && $canManage)) {
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('enrol_cart/view', [
        'cart' => $cart,
        'items' => $cart->items,
        'show_detail' => $canManage,
        'show_actions' => $cart->isCurrentUserOwner && ($cart->isCurrent || $cart->isCheckout),
        'checkout_url' => $cart->checkoutUrl,
        'cancel_url' => new moodle_url('/enrol/cart/cancel.php'),
        'session_key' => sesskey(),
    ]);
    echo $OUTPUT->footer();
    exit();
}

// Render the current cart view for the owner
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('enrol_cart/view_current', [
    'cart' => $cart,
    'items' => $cart->items,
    'checkout_url' => new moodle_url('/enrol/cart/checkout.php'),
    'can_remove_items' => $cart->canEditItems,
]);
echo $OUTPUT->footer();
