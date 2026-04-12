<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        // --- Users ---
        $users = [
            [
                'name'       => 'Admin POS',
                'email'      => 'admin@pos.com',
                'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Cashier One',
                'email'      => 'cashier@pos.com',
                'password'   => password_hash('cashier123', PASSWORD_DEFAULT),
                'role'       => 'cashier',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('users')->insertBatch($users);

        // --- Categories ---
        $categories = [
            ['name' => 'Beverages',    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Snacks',       'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Electronics',  'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Household',    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Personal Care','created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('categories')->insertBatch($categories);

        // --- Products ---
        // Images: picsum.photos/seed/{seed}/400/400
        // Each seed is fixed so the same product always shows the same image.
        // Width/height set to 400×400 for square product thumbnails.
        $products = [
            // Beverages (category_id: 1)
            [
                'name'        => 'Mineral Water 600ml',
                'sku'         => 'BEV-001',
                'price'       => 3000,
                'stock'       => 200,
                'category_id' => 1,
                'image'       => 'http://localhost:8080/uploads/mineral-water.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Green Tea Bottle',
                'sku'         => 'BEV-002',
                'price'       => 8000,
                'stock'       => 150,
                'category_id' => 1,
                'image'       => 'http://localhost:8080/uploads/green-tea-bottle.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Orange Juice 250ml',
                'sku'         => 'BEV-003',
                'price'       => 12000,
                'stock'       => 100,
                'category_id' => 1,
                'image'       => 'http://localhost:8080/uploads/orange-juice.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            // Snacks (category_id: 2)
            [
                'name'        => 'Potato Chips Original',
                'sku'         => 'SNK-001',
                'price'       => 15000,
                'stock'       => 80,
                'category_id' => 2,
                'image'       => 'http://localhost:8080/uploads/potato-chips.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Chocolate Wafer',
                'sku'         => 'SNK-002',
                'price'       => 5000,
                'stock'       => 120,
                'category_id' => 2,
                'image'       => 'http://localhost:8080/uploads/chocolate-wafer.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Instant Noodles',
                'sku'         => 'SNK-003',
                'price'       => 3500,
                'stock'       => 300,
                'category_id' => 2,
                'image'       => 'http://localhost:8080/uploads/instant-noodles.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            // Electronics (category_id: 3)
            [
                'name'        => 'USB Flash Drive 32GB',
                'sku'         => 'ELC-001',
                'price'       => 75000,
                'stock'       => 30,
                'category_id' => 3,
                'image'       => 'http://localhost:8080/uploads/usb-32gb.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Earphone Basic',
                'sku'         => 'ELC-002',
                'price'       => 45000,
                'stock'       => 25,
                'category_id' => 3,
                'image'       => 'http://localhost:8080/uploads/earphone.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            // Household (category_id: 4)
            [
                'name'        => 'Dish Soap 500ml',
                'sku'         => 'HSD-001',
                'price'       => 18000,
                'stock'       => 60,
                'category_id' => 4,
                'image'       => 'http://localhost:8080/uploads/dish-soap.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Laundry Detergent 1kg',
                'sku'         => 'HSD-002',
                'price'       => 25000,
                'stock'       => 50,
                'category_id' => 4,
                'image'       => 'http://localhost:8080/uploads/laundry-detergent.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            // Personal Care (category_id: 5)
            [
                'name'        => 'Shampoo 200ml',
                'sku'         => 'PRC-001',
                'price'       => 22000,
                'stock'       => 70,
                'category_id' => 5,
                'image'       => 'http://localhost:8080/uploads/shampoo.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Toothpaste 120g',
                'sku'         => 'PRC-002',
                'price'       => 12000,
                'stock'       => 90,
                'category_id' => 5,
                'image'       => 'http://localhost:8080/uploads/toothpaste.jpg',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('products')->insertBatch($products);

        // --- Customers ---
        $customers = [
            ['name' => 'Walk-in Customer', 'phone' => null,           'email' => null,               'address' => null,                  'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Budi Santoso',     'phone' => '081234567890', 'email' => 'budi@email.com',   'address' => 'Jl. Merdeka No. 10',  'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Siti Rahayu',      'phone' => '082345678901', 'email' => 'siti@email.com',   'address' => 'Jl. Sudirman No. 5',  'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Ahmad Fauzi',      'phone' => '083456789012', 'email' => 'ahmad@email.com',  'address' => 'Jl. Gatot Subroto 22','created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Dewi Lestari',     'phone' => '084567890123', 'email' => 'dewi@email.com',   'address' => 'Jl. Pahlawan No. 8',  'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('customers')->insertBatch($customers);

        // --- Discounts ---
        $discounts = [
            ['name' => 'No Discount',    'type' => 'percentage', 'value' => 0],
            ['name' => 'Member 5%',      'type' => 'percentage', 'value' => 5],
            ['name' => 'VIP 10%',        'type' => 'percentage', 'value' => 10],
            ['name' => 'Special 15%',    'type' => 'percentage', 'value' => 15],
            ['name' => 'Flat Rp 5.000',  'type' => 'fixed',      'value' => 5000],
            ['name' => 'Flat Rp 10.000', 'type' => 'fixed',      'value' => 10000],
            ['name' => 'Flat Rp 20.000', 'type' => 'fixed',      'value' => 20000],
        ];
        $this->db->table('discounts')->insertBatch($discounts);
    }
}
