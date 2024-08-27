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

namespace enrol_cart\helper;

defined('MOODLE_INTERNAL') || die();

use context_system;
use core_payment\account;
use core_payment\gateway;
use core_payment\helper;
use lang_string;
use moodle_url;

/**
 * Class PaymentHelper
 * Provides utility functions for managing payment gateways.
 */
class PaymentHelper
{
    /**
     * Retrieve the list of available currencies with language strings that the payment system supports.
     *
     * @return array An associative array of currency codes to language strings.
     */
    public static function getAvailableCurrencies(): array
    {
        $currencyCodes = helper::get_supported_currencies();

        $currencies = [];
        foreach ($currencyCodes as $code) {
            $currencies[$code] = new lang_string($code, 'core_currencies');
        }

        uasort($currencies, 'strcmp');

        return $currencies;
    }

    /**
     * Retrieve the list of available payment accounts.
     *
     * @return array An array of available payment accounts.
     */
    public static function getAvailablePaymentAccounts(): array
    {
        $context = context_system::instance();
        return helper::get_payment_accounts_menu($context);
    }

    /**
     * Retrieve the list of available payment gateways for a specific account and currency.
     *
     * @param int $accountId The ID of the payment account.
     * @param string $currency The currency code.
     * @return array An array of available payment gateways.
     */
    public static function getAvailablePaymentGateways(int $accountId, string $currency): array
    {
        $gateways = [];

        $account = new account($accountId);

        if (!$account->get('id') || !$account->get('enabled')) {
            return $gateways;
        }

        foreach ($account->get_gateways() as $plugin => $gateway) {
            if (!$gateway->get('enabled')) {
                continue;
            }
            /** @var gateway $className */
            $className = '\paygw_' . $plugin . '\gateway';

            $currencies = component_class_callback($className, 'get_supported_currencies', [], []);
            $pluginName = get_string('pluginname', 'paygw_' . $plugin);
            if (in_array($currency, $currencies)) {
                $gateways[$plugin] = $pluginName;
            }
        }

        return $gateways;
    }

    /**
     * Retrieve the list of allowed payment gateways.
     *
     * @return array An array of allowed payment gateways.
     */
    public static function getAllowedPaymentGateways(): array
    {
        global $CFG;
        $accountId = CartHelper::getConfig('payment_account');
        $currency = CartHelper::getConfig('payment_currency');
        $allowedGateways = explode(',', CartHelper::getConfig('payment_gateways'));
        $availableGateways = self::getAvailablePaymentGateways($accountId, $currency);

        $gateways = [];

        foreach ($allowedGateways as $plugin) {
            if (!isset($availableGateways[$plugin])) {
                continue;
            }

            $gateways[] = (object) [
                'name' => $plugin,
                'title' => $availableGateways[$plugin],
                'icon_url' => (new moodle_url('/theme/image.php', [
                    'theme' => $CFG->theme,
                    'component' => 'paygw_' . $plugin,
                    'image' => 'icon',
                ]))->out(),
                'selected' => false,
            ];
        }

        if (isset($gateways[0])) {
            $gateways[0]->selected = true;
        }

        return $gateways;
    }

    /**
     * Check if a given payment gateway is valid.
     *
     * This method verifies if the provided payment gateway name is within the list
     * of allowed payment gateways.
     *
     * @param string $gatewayName The name of the payment gateway to check.
     * @return bool Returns true if the payment gateway is valid, otherwise false.
     */
    public static function isPaymentGatewayValid(string $gatewayName): bool
    {
        foreach (self::getAllowedPaymentGateways() as $gateway) {
            if ($gateway->name === $gatewayName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a random payment gateway from the list of allowed gateways.
     *
     * This method returns the name of a randomly selected payment gateway from
     * the list of allowed payment gateways. If no gateways are available, it returns null.
     *
     * @return string|null The name of the random payment gateway, or null if no gateways are available.
     */
    public static function getRandPaymentGateway(): ?string
    {
        $allowedGateways = self::getAllowedPaymentGateways();

        if (!empty($allowedGateways)) {
            $randomIndex = rand(0, count($allowedGateways) - 1);
            return $allowedGateways[$randomIndex]->name;
        }

        return null;
    }
}
