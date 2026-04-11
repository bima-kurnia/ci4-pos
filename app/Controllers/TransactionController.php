<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionItemModel;
use App\Models\PaymentModel;
use CodeIgniter\Controller;

class TransactionController extends Controller
{
    protected TransactionModel     $transactionModel;
    protected TransactionItemModel $itemModel;
    protected PaymentModel         $paymentModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->itemModel        = new TransactionItemModel();
        $this->paymentModel     = new PaymentModel();

        helper(['form', 'url']);
    }

    public function index()
    {
        $filters = [
            'date_start' => $this->request->getGet('date_start'),
            'date_end'   => $this->request->getGet('date_end'),
            'status'     => $this->request->getGet('status'),
        ];

        $transactions = $this->transactionModel->getWithDetails(array_filter($filters));

        return view('transactions/index', [
            'title'        => 'Transactions',
            'transactions' => $transactions,
            'filters'      => $filters,
        ]);
    }

    public function show(int $id)
    {
        $transaction = $this->transactionModel->getDetail($id);

        if (!$transaction) {
            return redirect()->to('/transactions')->with('error', 'Transaction not found.');
        }

        return view('transactions/show', [
            'title'       => 'Transaction Detail',
            'transaction' => $transaction,
            'items'       => $this->itemModel->getByTransaction($id),
            'payment'     => $this->paymentModel->getByTransaction($id),
        ]);
    }

    public function cancel(int $id)
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->back()->with('error', 'Admin only.');
        }

        $transaction = $this->transactionModel->find($id);

        if (!$transaction || $transaction['status'] === 'cancelled') {
            return redirect()->to('/transactions')->with('error', 'Cannot cancel this transaction.');
        }

        $this->transactionModel->update($id, ['status' => 'cancelled']);
        
        return redirect()->to('/transactions/' . $id)->with('success', 'Transaction cancelled.');
    }
}