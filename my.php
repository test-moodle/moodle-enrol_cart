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

$page = optional_param('page', 0, PARAM_INT);
$perPage = optional_param('perpage', 20, PARAM_INT);
$userId = $USER->id;

require_login();

$title = get_string('my_purchases', 'enrol_cart');
$url = new moodle_url('/enrol/cart/my.php');
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagetype('cart');
$PAGE->set_url($url);

$PAGE->navigation->add($title, $url, navigation_node::TYPE_CONTAINER);

/** @var Cart[] $carts */
$carts = Cart::findAllByUserId($userId, $page, $perPage);
$total = Cart::countAllByUserId($userId);

$table = new html_table();
$table->head = [
    '#',
    get_string('order_id', 'enrol_cart'),
    get_string('date', 'enrol_cart'),
    get_string('discount', 'enrol_cart'),
    get_string('payable', 'enrol_cart'),
    get_string('cart_status', 'enrol_cart'),
    '',
];
$table->attributes = ['class' => 'generaltable'];
$table->data = [];

$i = 0;
foreach ($carts as $cart) {
    $i++;
    $actions = [
        html_writer::tag('a', get_string('view', 'enrol_cart'), [
            'href' => $cart->viewUrl,
        ]),
    ];
    if ($cart->isCheckout) {
        $actions[] = html_writer::tag('a', get_string('pay', 'enrol_cart'), [
            'href' => $cart->checkoutUrl,
        ]);
    }
    $table->data[] = [
        $i,
        $cart->id,
        userdate($cart->checkout_at ?: $cart->created_at),
        $cart->finalDiscountAmount
            ? html_writer::tag('span', $cart->finalDiscountAmountFormatted, [
                'class' => 'currency text-danger',
            ])
            : '-',
        html_writer::tag('span', $cart->finalPayableFormatted, [
            'class' => 'currency',
        ]),
        $cart->statusNameFormattedHtml,
        implode(' | ', $actions),
    ];
}

if (empty($table->data)) {
    $cell = new html_table_cell(
        html_writer::tag('div', get_string('no_items', 'enrol_cart'), [
            'class' => 'text-center',
        ]),
    );
    $cell->colspan = 7;
    $table->data[] = new html_table_row([$cell]);
}

echo $OUTPUT->header();
// table
echo html_writer::table($table);
// pagination
echo $OUTPUT->paging_bar($total, $page, $perPage, $url);
echo $OUTPUT->footer();
