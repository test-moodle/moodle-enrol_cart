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

use core\output\notification;
use enrol_cart\helper\CartHelper;
use enrol_cart\helper\CouponHelper;
use enrol_cart\helper\PaymentHelper;

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('enrol_cart_settings', '', get_string('pluginname_desc', 'enrol_cart')));

    $account = CartHelper::getConfig('payment_account');
    $currency = CartHelper::getConfig('payment_currency');
    // available payment accounts
    $availableAccounts = PaymentHelper::getAvailablePaymentAccounts();
    // available currencies
    $availableCurrencies = PaymentHelper::getAvailableCurrencies();
    // available payment gateways
    $availableGateways = $account && $currency ? PaymentHelper::getAvailablePaymentGateways($account, $currency) : [];

    // no payment account warning
    if (empty($availableAccounts)) {
        $notify = new notification(
            get_string('error_no_payment_accounts_available', 'enrol_cart'),
            notification::NOTIFY_WARNING,
        );
        $settings->add(new admin_setting_heading('enrol_cart_no_payment_account', '', $OUTPUT->render($notify)));
    } else {
        // payment account
        $settings->add(
            new admin_setting_configselect(
                'enrol_cart/payment_account',
                get_string('payment_account', 'enrol_cart'),
                '',
                '',
                $availableAccounts,
            ),
        );
    }

    // no payment currency warning
    if (empty($availableCurrencies)) {
        $notify = new notification(
            get_string('error_no_payment_currency_available', 'enrol_cart'),
            notification::NOTIFY_WARNING,
        );
        $settings->add(new admin_setting_heading('enrol_cart_no_payment_currency', '', $OUTPUT->render($notify)));
    } else {
        // payment currency
        $settings->add(
            new admin_setting_configselect(
                'enrol_cart/payment_currency',
                get_string('payment_currency', 'enrol_cart'),
                '',
                '',
                $availableCurrencies,
            ),
        );
    }

    // no payment gateways warning
    if (empty($availableGateways)) {
        $notify = new notification(
            get_string('error_no_payment_gateway_available', 'enrol_cart'),
            notification::NOTIFY_WARNING,
        );
        $settings->add(new admin_setting_heading('enrol_cart_no_payment_gateway', '', $OUTPUT->render($notify)));
    } else {
        // payment gateways
        $settings->add(
            new admin_setting_configmultiselect(
                'enrol_cart/payment_gateways',
                get_string('payment_gateways', 'enrol_cart'),
                get_string('payment_gateways_desc', 'enrol_cart'),
                [],
                $availableGateways,
            ),
        );
    }

    // auto select payment gateway
    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_cart/auto_select_payment_gateway',
            get_string('auto_select_payment_gateway', 'enrol_cart'),
            get_string('auto_select_payment_gateway_desc', 'enrol_cart'),
            false,
        ),
    );

    // coupon enable
    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_cart/coupon_enable',
            get_string('coupon_enable', 'enrol_cart'),
            get_string('coupon_enable_desc', 'enrol_cart'),
            0,
        ),
    );

    // coupon class
    $settings->add(
        new admin_setting_configtext(
            'enrol_cart/coupon_class',
            get_string('coupon_class', 'enrol_cart'),
            get_string('coupon_class_desc', 'enrol_cart'),
            '',
        ),
    );

    $couponClass = CouponHelper::getCouponClassName();
    $couponClassError = null;
    if (!empty($couponClass)) {
        if (!class_exists($couponClass)) {
            $couponClassError = get_string('error_coupon_class_not_found', 'enrol_cart');
        } elseif (!in_array('enrol_cart\object\CouponInterface', class_implements($couponClass))) {
            $couponClassError = get_string('error_coupon_class_not_implemented', 'enrol_cart');
        }
    }

    if (!empty($couponClassError)) {
        $notify = new notification($couponClassError, notification::NOTIFY_WARNING);
        $settings->add(new admin_setting_heading('error_coupon_class_error', '', $OUTPUT->render($notify)));
    }

    // payment completion window
    $settings->add(
        new admin_setting_configduration(
            'enrol_cart/payment_completion_time',
            get_string('payment_completion_time', 'enrol_cart'),
            get_string('payment_completion_time_desc', 'enrol_cart'),
            60 * 15,
        ),
    );

    // canceled cart lifetime
    $settings->add(
        new admin_setting_configduration(
            'enrol_cart/canceled_cart_lifetime',
            get_string('canceled_cart_lifetime', 'enrol_cart'),
            get_string('canceled_cart_lifetime_desc', 'enrol_cart'),
            0,
        ),
    );

    // pending payment cart lifetime
    $settings->add(
        new admin_setting_configduration(
            'enrol_cart/pending_payment_cart_lifetime',
            get_string('pending_payment_cart_lifetime', 'enrol_cart'),
            get_string('pending_payment_cart_lifetime_desc', 'enrol_cart'),
            0,
        ),
    );

    // not delete cart with payment record
    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_cart/not_delete_cart_with_payment_record',
            get_string('not_delete_cart_with_payment_record', 'enrol_cart'),
            get_string('not_delete_cart_with_payment_record_desc', 'enrol_cart'),
            true,
        ),
    );

    // verify payment on delivery
    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_cart/verify_payment_on_delivery',
            get_string('verify_payment_on_delivery', 'enrol_cart'),
            get_string('verify_payment_on_delivery_desc', 'enrol_cart'),
            true,
        ),
    );

    // convert IRR to IRT
    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_cart/convert_irr_to_irt',
            get_string('convert_irr_to_irt', 'enrol_cart'),
            get_string('convert_irr_to_irt_desc', 'enrol_cart'),
            true,
        ),
    );

    // convert numbers to persian
    $settings->add(
        new admin_setting_configcheckbox(
            'enrol_cart/convert_numbers_to_persian',
            get_string('convert_numbers_to_persian', 'enrol_cart'),
            get_string('convert_numbers_to_persian_desc', 'enrol_cart'),
            true,
        ),
    );

    $settings->add(
        new admin_setting_heading(
            'enrol_cart_defaults',
            get_string('enrol_instance_defaults', 'enrol_cart'),
            get_string('enrol_instance_defaults_desc', 'enrol_cart'),
        ),
    );

    // default status
    $settings->add(
        new admin_setting_configselect(
            'enrol_cart/status',
            get_string('status', 'enrol_cart'),
            get_string('status_desc', 'enrol_cart'),
            ENROL_INSTANCE_DISABLED,
            enrol_get_plugin('cart')->getStatusOptions(),
        ),
    );

    // default role
    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(
            new admin_setting_configselect(
                'enrol_cart/assign_role',
                get_string('assign_role', 'enrol_cart'),
                get_string('assign_role_desc', 'enrol_cart'),
                $student->id,
                $options,
            ),
        );
    }

    // enrol period
    $settings->add(
        new admin_setting_configduration(
            'enrol_cart/enrol_period',
            get_string('enrol_period', 'enrol_cart'),
            get_string('enrol_period_desc', 'enrol_cart'),
            0,
        ),
    );
}
