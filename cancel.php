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

use enrol_cart\object\Cart;

require_once '../../config.php';

global $PAGE, $OUTPUT, $CFG, $USER;

// Retrieve the cart ID from the request parameters
$cartId = required_param('id', PARAM_ALPHANUM);

// Ensure the user is logged in and the session is valid
require_login();
require_sesskey();

// Set the page title and URL
$title = get_string('pluginname', 'enrol_cart') . ' - ' . get_string('cancel', 'enrol_cart');
$url = new moodle_url('/enrol/cart/cancel.php');
$context = context_system::instance();

// Configure the page settings
$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagetype('cart');
$PAGE->set_url($url);

// Fetch the cart object using the provided cart ID
$cart = Cart::findOne($cartId);

// Check if the cart exists, is not empty, can be edited, and belongs to the current user
if ($cart && !$cart->isEmpty && $cart->canEditItems && $cart->isCurrentUserOwner) {
    // Cancel the cart
    $cart->cancel();
}

// Redirect the user to the cart view URL after cancellation
redirect($cart->viewUrl);
exit();
