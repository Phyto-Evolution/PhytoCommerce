<?php
if (!defined('_PS_VERSION_')) exit;

class PhytoErpApi {

    private $base_url;
    private $api_key;
    private $api_secret;

    public function __construct($base_url, $api_key, $api_secret) {
        $this->base_url   = rtrim($base_url, '/');
        $this->api_key    = $api_key;
        $this->api_secret = $api_secret;
    }

    // ── Public sync methods (static entry points used by hooks) ──────────────

    public static function syncCustomer(PhytoErpApi $api, $customer) {
        $name = $customer->firstname . ' ' . $customer->lastname;
        // Check if customer already exists in ERPNext by email
        $existing = $api->request('GET', '/api/resource/Customer', [
            'filters' => json_encode([['Customer', 'email_id', '=', $customer->email]]),
            'fields'  => '["name"]',
        ]);
        if (!empty($existing['data'])) {
            self::log('customer', 'push', $customer->id, $existing['data'][0]['name'], 'skipped', 'Already exists');
            return;
        }
        $result = $api->request('POST', '/api/resource/Customer', [
            'customer_name'  => $name,
            'customer_type'  => 'Individual',
            'customer_group' => 'Individual',
            'territory'      => 'India',
            'email_id'       => $customer->email,
            'mobile_no'      => $customer->phone ?? '',
        ]);
        if (isset($result['data']['name'])) {
            self::log('customer', 'push', $customer->id, $result['data']['name'], 'success', 'Synced ' . $name);
        } else {
            $err = $result['exception'] ?? $result['error'] ?? 'Unknown error';
            self::log('customer', 'push', $customer->id, $name, 'error', $err);
        }
    }

    public static function syncOrder(PhytoErpApi $api, Order $order, $id_lang) {
        $customer  = new Customer($order->id_customer);
        $items     = $order->getProducts();
        $erp_items = [];
        foreach ($items as $item) {
            $erp_items[] = [
                'item_code'   => $item['product_reference'] ?: 'PS-' . $item['product_id'],
                'item_name'   => $item['product_name'],
                'qty'         => (float)$item['product_quantity'],
                'rate'        => (float)$item['unit_price_tax_excl'],
                'uom'         => 'Nos',
            ];
        }
        $customer_name = $customer->firstname . ' ' . $customer->lastname;
        $result = $api->request('POST', '/api/resource/Sales Order', [
            'customer'           => $customer_name,
            'transaction_date'   => date('Y-m-d', strtotime($order->date_add)),
            'delivery_date'      => date('Y-m-d', strtotime($order->date_add . ' +7 days')),
            'currency'           => 'INR',
            'custom_ps_order_id' => $order->id,
            'custom_ps_reference'=> $order->reference,
            'items'              => $erp_items,
        ]);
        if (isset($result['data']['name'])) {
            self::log('order', 'push', $order->id, $result['data']['name'], 'success', 'Order ' . $order->reference);
        } else {
            $err = $result['exception'] ?? $result['error'] ?? 'Unknown error';
            self::log('order', 'push', $order->id, $order->reference, 'error', $err);
        }
    }

    public static function syncProduct(PhytoErpApi $api, $product, $id_lang) {
        $name = is_array($product->name) ? ($product->name[$id_lang] ?? reset($product->name)) : $product->name;
        $ref  = $product->reference ?: 'PS-' . $product->id;
        // Check if item exists
        $existing = $api->request('GET', '/api/resource/Item/' . urlencode($ref));
        if (isset($existing['data'])) {
            // Update rate
            $api->request('PUT', '/api/resource/Item/' . urlencode($ref), [
                'standard_rate' => (float)$product->price,
            ]);
            self::log('product', 'push', $product->id, $ref, 'updated', 'Updated ' . $name);
            return;
        }
        $result = $api->request('POST', '/api/resource/Item', [
            'item_code'      => $ref,
            'item_name'      => $name,
            'item_group'     => 'Products',
            'stock_uom'      => 'Nos',
            'standard_rate'  => (float)$product->price,
            'is_sales_item'  => 1,
            'is_stock_item'  => 1,
        ]);
        if (isset($result['data']['name'])) {
            self::log('product', 'push', $product->id, $result['data']['name'], 'success', 'Synced ' . $name);
        } else {
            $err = $result['exception'] ?? $result['error'] ?? 'Unknown error';
            self::log('product', 'push', $product->id, $ref, 'error', $err);
        }
    }

    public static function pullInvoices(PhytoErpApi $api, $from_date) {
        $result = $api->request('GET', '/api/resource/Sales Invoice', [
            'filters' => json_encode([['Sales Invoice', 'posting_date', '>=', $from_date]]),
            'fields'  => '["name","custom_ps_order_id","status","grand_total","posting_date"]',
            'limit'   => 100,
        ]);
        $synced = 0;
        if (empty($result['data'])) return $synced;
        foreach ($result['data'] as $invoice) {
            $ps_order_id = (int)($invoice['custom_ps_order_id'] ?? 0);
            if (!$ps_order_id) continue;
            // Mark PS order with ERP invoice reference
            Configuration::updateValue(
                'PHYTO_ERP_INV_' . $ps_order_id,
                json_encode(['invoice' => $invoice['name'], 'date' => $invoice['posting_date']])
            );
            self::log('invoice', 'pull', $ps_order_id, $invoice['name'], 'success', 'Invoice pulled');
            $synced++;
        }
        return $synced;
    }

    // ── Manual full-sync methods ─────────────────────────────────────────────

    public function manualSyncAllOrders($id_lang) {
        $orders = Order::getOrdersWithInformations();
        $count  = 0;
        foreach ($orders as $order_data) {
            $order = new Order($order_data['id_order']);
            if (Validate::isLoadedObject($order)) {
                self::syncOrder($this, $order, $id_lang);
                $count++;
            }
        }
        return $count;
    }

    public function manualSyncAllCustomers() {
        $customers = Customer::getCustomers();
        $count     = 0;
        foreach ($customers as $c) {
            $customer = new Customer($c['id_customer']);
            if (Validate::isLoadedObject($customer)) {
                self::syncCustomer($this, $customer);
                $count++;
            }
        }
        return $count;
    }

    public function manualSyncAllProducts($id_lang) {
        $products = Product::getProducts($id_lang, 0, 0, 'id_product', 'ASC');
        $count    = 0;
        foreach ($products as $p) {
            $product = new Product($p['id_product'], false, $id_lang);
            if (Validate::isLoadedObject($product)) {
                self::syncProduct($this, $product, $id_lang);
                $count++;
            }
        }
        return $count;
    }

    // ── Core HTTP client ─────────────────────────────────────────────────────

    public function request($method, $endpoint, $data = []) {
        $url = $this->base_url . $endpoint;
        if ($method === 'GET' && !empty($data)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: token ' . $this->api_key . ':' . $this->api_secret,
            ],
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($curl_err) return ['error' => $curl_err, 'code' => 0];
        $decoded = json_decode($response, true);
        if ($code >= 400) {
            return ['error' => $decoded['exception'] ?? $decoded['message'] ?? 'HTTP ' . $code, 'code' => $code];
        }
        return $decoded ?: ['raw' => $response];
    }

    // ── Logging ──────────────────────────────────────────────────────────────

    public static function log($type, $direction, $ps_id, $erp_name, $status, $message = '') {
        Db::getInstance()->insert('phyto_erp_sync_log', [
            'sync_type'  => pSQL($type),
            'direction'  => pSQL($direction),
            'ps_id'      => (int)$ps_id,
            'erp_name'   => pSQL((string)$erp_name),
            'status'     => pSQL($status),
            'message'    => pSQL($message),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        // Keep only last 200 rows
        Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'phyto_erp_sync_log`
             WHERE id NOT IN (
                 SELECT id FROM (
                     SELECT id FROM `' . _DB_PREFIX_ . 'phyto_erp_sync_log`
                     ORDER BY id DESC LIMIT 200
                 ) t
             )'
        );
    }

    public static function getLog($limit = 50) {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_erp_sync_log`
             ORDER BY id DESC LIMIT ' . (int)$limit
        ) ?: [];
    }
}
