<?php

namespace Database\Seeders;

use App\Models\Raspadinha;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RaspadinhaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $raspadinhas = [
            [
                'name' => 'Raspadinha Básica',
                'image' => 'scratch_cards/1.png',
                'description' => 'Ganhe até R$ 2.000,00',
                'price' => 'R$ 1,00',
                'max_prize' => 'R$ 2.000',
                'category' => 'dinheiro',
                'backend_cost' => 1.00,
                'is_active' => true,
                'sort_order' => 1,
                'win_chance_percentage' => 5,
            ],
            [
                'name' => 'Raspadinha Cinco Mil',
                'image' => 'scratch_cards/2.png',
                'description' => 'Ganhe até R$ 5.000,00',
                'price' => 'R$ 2,00',
                'max_prize' => 'R$ 5.000',
                'category' => 'dinheiro',
                'backend_cost' => 2.00,
                'is_active' => true,
                'sort_order' => 2,
                'win_chance_percentage' => 8,
            ],
            [
                'name' => 'Raspadinha Dez Mil',
                'image' => 'scratch_cards/3.png',
                'description' => 'Ganhe até R$ 10.000,00',
                'price' => 'R$ 5,00',
                'max_prize' => 'R$ 10.000',
                'category' => 'dinheiro',
                'backend_cost' => 5.00,
                'is_active' => true,
                'sort_order' => 3,
                'win_chance_percentage' => 12,
            ],
            [
                'name' => 'Raspadinha Make',
                'image' => 'scratch_cards/5.png',
                'description' => 'Ganhe até R$ 14.000,00',
                'price' => 'R$ 25,00',
                'max_prize' => 'R$ 14.000',
                'category' => 'misto',
                'backend_cost' => 25.00,
                'is_active' => true,
                'sort_order' => 4,
                'win_chance_percentage' => 15,
            ],
            [
                'name' => 'Raspadinha Milhão',
                'image' => 'scratch_cards/4.png',
                'description' => 'Ganhe até R$ 10.000,00',
                'price' => 'R$ 50,00',
                'max_prize' => 'R$ 10.000',
                'category' => 'misto',
                'backend_cost' => 50.00,
                'is_active' => true,
                'sort_order' => 5,
                'win_chance_percentage' => 20,
            ],
            [
                'name' => 'Raspadinha 6',
                'image' => 'scratch_cards/6.png',
                'description' => 'Ganhe até R$ 14.000,00',
                'price' => 'R$ 60,00',
                'max_prize' => 'R$ 14.000',
                'category' => 'misto',
                'backend_cost' => 60.00,
                'is_active' => true,
                'sort_order' => 6,
                'win_chance_percentage' => 25,
            ],
            [
                'name' => 'Raspadinha 7',
                'image' => 'scratch_cards/7.png',
                'description' => 'Ganhe até R$ 14.000,00',
                'price' => 'R$ 80,00',
                'max_prize' => 'R$ 14.000',
                'category' => 'produtos',
                'backend_cost' => 80.00,
                'is_active' => true,
                'sort_order' => 7,
                'win_chance_percentage' => 30,
            ],
            [
                'name' => 'Raspadinha 8',
                'image' => 'scratch_cards/8.png',
                'description' => 'Ganhe até R$ 14.000,00',
                'price' => 'R$ 100,00',
                'max_prize' => 'R$ 14.000',
                'category' => 'produtos',
                'backend_cost' => 100.00,
                'is_active' => true,
                'sort_order' => 8,
                'win_chance_percentage' => 35,
            ],
            [
                'name' => 'Raspadinha 9',
                'image' => 'scratch_cards/9.png',
                'description' => 'Ganhe até R$ 14.000,00',
                'price' => 'R$ 120,00',
                'max_prize' => 'R$ 14.000',
                'category' => 'produtos',
                'backend_cost' => 120.00,
                'is_active' => true,
                'sort_order' => 9,
                'win_chance_percentage' => 40,
            ],
        ];

        foreach ($raspadinhas as $raspadinha) {
            Raspadinha::create($raspadinha);
        }
    }
}
