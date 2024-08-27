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

namespace enrol_cart\form;

defined('MOODLE_INTERNAL') || die();

use enrol_cart\object\Cart;
use moodleform;

require_once "$CFG->libdir/formslib.php";

class CouponCodeForm extends moodleform
{
    /**
     * @inheritDoc
     */
    protected function definition()
    {
        $form = $this->_form;

        $cart = $this->_customdata['cart'];
        if (!$cart instanceof Cart) {
            print_r('Invalid cart');
        }
        $this->set_data(['coupon_code' => $cart->coupon_code]);

        $form->addElement('hidden', 'id', $cart->id);
        $form->setType('id', PARAM_INT);

        $form->addElement('text', 'coupon_code', '', [
            'placeholder' => get_string('coupon_code', 'enrol_cart'),
        ]);
        $form->setType('coupon_code', PARAM_ALPHANUMEXT);

        $this->add_action_buttons(true, get_string('apply', 'enrol_cart'));
    }
}
