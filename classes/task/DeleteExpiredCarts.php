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

namespace enrol_cart\task;

use context_system;
use core\task\scheduled_task;
use enrol_cart\event\cart_deleted;
use enrol_cart\helper\CartHelper;
use enrol_cart\helper\CouponHelper;
use enrol_cart\object\Cart;
use enrol_cart\object\CartDto;
use enrol_cart\object\CartStatusInterface;

/**
 * Class DeleteExpiredCarts
 * Scheduled task for deleting expired shopping carts in the enrol_cart plugin.
 */
class DeleteExpiredCarts extends scheduled_task
{
    /**
     * Get the name of the scheduled task.
     *
     * @return string The localized name of the task.
     */
    public function get_name()
    {
        return get_string('delete_expired_carts', 'enrol_cart');
    }

    /**
     * Execute the scheduled task.
     * This function deletes both canceled and pending payment carts that have expired.
     */
    public function execute()
    {
        $this->processDeleteCanceledCarts();
        $this->processDeletePendingPaymentCarts();
    }

    /**
     * Process deletion of canceled carts that have expired.
     * It deletes carts with a 'canceled' status that have not been updated within the configured lifetime.
     */
    protected function processDeleteCanceledCarts()
    {
        $time = $this->getTime('canceled_cart_lifetime');
        if (!$time) {
            return;
        }

        global $DB;

        $carts = $DB->get_records_sql('SELECT * FROM {enrol_cart} WHERE status = :status AND updated_at < :time', [
            'status' => CartStatusInterface::STATUS_CANCELED,
            'time' => $time,
        ]);

        $this->processDeleteCarts($carts);
    }

    /**
     * Process deletion of pending payment carts that have expired.
     * It deletes carts with a 'checkout' status that have not been checked out within the configured lifetime.
     */
    protected function processDeletePendingPaymentCarts()
    {
        $time = $this->getTime('pending_payment_cart_lifetime');
        if (!$time) {
            return;
        }

        global $DB;

        $carts = $DB->get_records_sql('SELECT * FROM {enrol_cart} WHERE status = :status AND checkout_at < :time', [
            'status' => CartStatusInterface::STATUS_CHECKOUT,
            'time' => $time,
        ]);

        $this->processDeleteCarts($carts);
    }

    /**
     * Get the expiration time for a specific cart status.
     *
     * @param string $item The configuration item for the cart lifetime.
     * @return int The timestamp for expiration or 0 if not configured.
     */
    protected function getTime(string $item): int
    {
        $lifetime = (int) CartHelper::getConfig($item);
        if (!$lifetime) {
            return 0;
        }

        return time() - $lifetime;
    }

    /**
     * Check if a cart has associated payment records.
     *
     * @param int $cartId The ID of the cart.
     * @return bool True if the cart has payment records, false otherwise.
     */
    protected function hasPaymentRecord(int $cartId): bool
    {
        global $DB;
        return $DB->record_exists('payments', [
            'component' => 'enrol_cart',
            'paymentarea' => 'cart',
            'itemid' => $cartId,
        ]);
    }

    /**
     * Delete a cart and its associated items.
     *
     * @param object $cart The object of the cart to delete.
     */
    private function deleteCart(object $cart)
    {
        global $DB;

        $systemContext = context_system::instance();
        $cart = Cart::populateOne($cart);

        if ($cart->coupon_id && $cart->coupon_usage_id) {
            CouponHelper::couponCancel(new CartDto($cart));
        }

        $DB->delete_records('enrol_cart_items', ['cart_id' => $cart->id]);
        $DB->delete_records('enrol_cart', ['id' => $cart->id]);

        // Trigger cart deleted event
        $event = cart_deleted::create([
            'context' => $systemContext,
            'objectid' => $cart->id,
            'other' => (array) $cart->getAttributes(),
        ]);
        $event->trigger();
    }

    /**
     * Process deletion of carts.
     * It deletes each cart in the provided list if it has no associated payment records.
     *
     * @param array $carts The list of carts to process.
     */
    private function processDeleteCarts(array $carts)
    {
        foreach ($carts as $cart) {
            if (CartHelper::getConfig('not_delete_cart_with_payment_record') && $this->hasPaymentRecord($cart->id)) {
                continue;
            }
            $this->deleteCart($cart);
        }
    }
}
