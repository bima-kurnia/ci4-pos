<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CustomerModel;
use App\Models\DiscountModel;
use App\Models\TransactionModel;
use App\Models\TransactionItemModel;
use App\Models\PaymentModel;
use App\Models\StockMovementModel;
use CodeIgniter\Controller;

class PosController extends Controller
{
    protected ProductModel         $productModel;
    protected CustomerModel        $customerModel;
    protected DiscountModel        $discountModel;
    protected TransactionModel     $transactionModel;
    protected TransactionItemModel $itemModel;
    protected PaymentModel         $paymentModel;
    protected StockMovementModel   $stockModel;

    // Tax rate (11%)
    const TAX_RATE = 0.11;

    public function __construct()
    {
        $this->productModel     = new ProductModel();
        $this->customerModel    = new CustomerModel();
        $this->discountModel    = new DiscountModel();
        $this->transactionModel = new TransactionModel();
        $this->itemModel        = new TransactionItemModel();
        $this->paymentModel     = new PaymentModel();
        $this->stockModel       = new StockMovementModel();
        
        helper(['form', 'url']);
    }

    /**
     * POS main page
     */
    public function index()
    {
        return view('pos/index', [
            'title'     => 'Point of Sale',
            'customers' => $this->customerModel->orderBy('name')->findAll(),
            'discounts' => $this->discountModel->findAll(),
            'products'  => $this->productModel->getWithCategory(),
            'tax_rate'  => self::TAX_RATE,
        ]);
    }

    /**
     * AJAX: Checkout — the core transactional logic
     *
     * Request JSON body:
     * {
     *   "customer_id": 1,          // nullable
     *   "discount_id": 2,          // nullable
     *   "payment_method": "cash",
     *   "amount_paid": 100000,
     *   "cart": [
     *     { "product_id": 1, "quantity": 2 },
     *     ...
     *   ]
     * }
     */
    public function checkout()
    {
        $json = $this->request->getJSON(true);

        // --- Basic validation ---
        if (empty($json['cart'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cart is empty.']);
        }

        $db = \Config\Database::connect();

        // =========================================================
        // BEGIN DATABASE TRANSACTION
        // =========================================================
        $db->transBegin();

        try {
            $cart        = $json['cart'];
            $customerId  = !empty($json['customer_id']) ? (int) $json['customer_id'] : null;
            $discountId  = !empty($json['discount_id']) ? (int) $json['discount_id'] : null;
            $method      = $json['payment_method'] ?? 'cash';
            $amountPaid  = (float) ($json['amount_paid'] ?? 0);

            // ----------------------------------------------------------
            // 1. Validate stock & build line items
            // ----------------------------------------------------------
            $lineItems   = [];
            $totalAmount = 0.0;

            foreach ($cart as $cartItem) {
                $productId = (int) $cartItem['product_id'];
                $qty       = (int) $cartItem['quantity'];

                if ($qty <= 0) {
                    throw new \Exception("Invalid quantity for product ID {$productId}.");
                }

                // Lock row for update to prevent race conditions
                $product = $db->table('products')
                    ->where('id', $productId)
                    ->get()->getRowArray();

                if (!$product) {
                    throw new \Exception("Product ID {$productId} not found.");
                }

                // *** Prevent negative stock ***
                if ($product['stock'] < $qty) {
                    throw new \Exception(
                        "Insufficient stock for \"{$product['name']}\". Available: {$product['stock']}, Requested: {$qty}."
                    );
                }

                $subtotal = (float) $product['price'] * $qty;

                $lineItems[] = [
                    'product_id' => $productId,
                    'price'      => $product['price'],
                    'quantity'   => $qty,
                    'subtotal'   => $subtotal,
                    'name'       => $product['name'],
                    'stock'      => $product['stock'],
                ];

                $totalAmount += $subtotal;
            }

            // ----------------------------------------------------------
            // 2. Calculate discount
            // ----------------------------------------------------------
            $discountAmount = 0.0;

            if ($discountId) {
                $discount = $this->discountModel->find($discountId);

                if ($discount) {
                    $discountAmount = $this->discountModel->calculate($discount, $totalAmount);
                }
            }

            $afterDiscount = $totalAmount - $discountAmount;

            // ----------------------------------------------------------
            // 3. Calculate tax
            // ----------------------------------------------------------
            $taxAmount  = round($afterDiscount * self::TAX_RATE, 2);
            $grandTotal = round($afterDiscount + $taxAmount, 2);

            // ----------------------------------------------------------
            // 4. Validate payment amount
            // ----------------------------------------------------------
            if ($amountPaid < $grandTotal) {
                throw new \Exception(
                    sprintf('Insufficient payment. Grand total: %s, Paid: %s.',
                        number_format($grandTotal, 0, ',', '.'),
                        number_format($amountPaid, 0, ',', '.')
                    )
                );
            }

            $changeAmount = round($amountPaid - $grandTotal, 2);

            // ----------------------------------------------------------
            // 5. Generate invoice number
            // ----------------------------------------------------------
            $invoiceNumber = $this->transactionModel->generateInvoiceNumber();

            // ----------------------------------------------------------
            // 6. Insert transaction
            // ----------------------------------------------------------
            $transactionId = $db->table('transactions')->insert([
                'invoice_number' => $invoiceNumber,
                'user_id'        => session()->get('user_id'),
                'customer_id'    => $customerId,
                'total_amount'   => $totalAmount,
                'discount'       => $discountAmount,
                'tax'            => $taxAmount,
                'grand_total'    => $grandTotal,
                'status'         => 'completed',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            $transactionId = $db->insertID();

            // ----------------------------------------------------------
            // 7. Insert transaction items, reduce stock, record movements
            // ----------------------------------------------------------
            foreach ($lineItems as $item) {
                // Insert transaction item
                $db->table('transaction_items')->insert([
                    'transaction_id' => $transactionId,
                    'product_id'     => $item['product_id'],
                    'price'          => $item['price'],
                    'quantity'       => $item['quantity'],
                    'subtotal'       => $item['subtotal'],
                ]);

                // Reduce stock (double-check negativity — paranoid guard)
                $newStock = $item['stock'] - $item['quantity'];

                if ($newStock < 0) {
                    throw new \Exception("Stock would go negative for \"{$item['name']}\".");
                }

                $db->table('products')
                    ->where('id', $item['product_id'])
                    ->update(['stock' => $newStock, 'updated_at' => date('Y-m-d H:i:s')]);

                // Record stock movement
                $db->table('stock_movements')->insert([
                    'product_id' => $item['product_id'],
                    'type'       => 'out',
                    'quantity'   => $item['quantity'],
                    'reference'  => $invoiceNumber,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // ----------------------------------------------------------
            // 8. Insert payment record
            // ----------------------------------------------------------
            $db->table('payments')->insert([
                'transaction_id' => $transactionId,
                'method'         => $method,
                'amount_paid'    => $amountPaid,
                'change_amount'  => $changeAmount,
                'payment_status' => 'paid',
                'reference'      => null,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            // =========================================================
            // COMMIT
            // =========================================================
            $db->transCommit();

            return $this->response->setJSON([
                'success'        => true,
                'message'        => 'Transaction completed successfully!',
                'transaction_id' => $transactionId,
                'invoice_number' => $invoiceNumber,
                'grand_total'    => $grandTotal,
                'amount_paid'    => $amountPaid,
                'change_amount'  => $changeAmount,
                'redirect'       => base_url('/transactions/' . $transactionId),
            ]);

        } catch (\Exception $e) {
            // =========================================================
            // ROLLBACK on any error
            // =========================================================
            $db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * AJAX: recalculate totals (discount + tax preview)
     */
    public function calculate()
    {
        $json           = $this->request->getJSON(true);
        $subtotal       = (float) ($json['subtotal'] ?? 0);
        $discountId     = (int) ($json['discount_id'] ?? 0);
        $discountAmount = 0.0;

        if ($discountId) {
            $discount = $this->discountModel->find($discountId);

            if ($discount) {
                $discountAmount = $this->discountModel->calculate($discount, $subtotal);
            }
        }

        $afterDiscount = $subtotal - $discountAmount;
        $tax           = round($afterDiscount * self::TAX_RATE, 2);
        $grandTotal    = round($afterDiscount + $tax, 2);

        return $this->response->setJSON([
            'subtotal'    => $subtotal,
            'discount'    => $discountAmount,
            'tax'         => $tax,
            'grand_total' => $grandTotal,
        ]);
    }
}