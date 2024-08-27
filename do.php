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

require_once '../../config.php';

$action = required_param('action', PARAM_ALPHANUMEXT);

// the cart enrolment disabled
if (!enrol_is_enabled('cart')) {
    print_error('error_disabled', 'enrol_cart');
}

// add or remove an item or a course
if ($action == 'add' || $action == 'remove') {
    $instanceId = optional_param('instance', null, PARAM_INT);
    $courseId = optional_param('course', null, PARAM_INT);

    if (!$instanceId && !$courseId) {
        print_error('CourseID or InstanceID is required.');
    }

    if ($action == 'add') {
        if ($instanceId) {
            CartHelper::addInstanceToCart($instanceId);
        } elseif ($courseId) {
            CartHelper::addCourseToCart($courseId);
        }
    } elseif ($action == 'remove') {
        if ($instanceId) {
            CartHelper::removeInstanceFromCart($instanceId);
        } elseif ($courseId) {
            CartHelper::removeCourseFromCart($courseId);
        }
    }
}

redirect(CartHelper::getCartViewUrl());
