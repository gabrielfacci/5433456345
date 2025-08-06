<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vip;

class VipLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array com os novos níveis VIP
        $vipLevels = [
            [
                'bet_level' => 4,
                'title' => 'Ouro',
                'description' => 'Status VIP Ouro com benefícios exclusivos',
                'bet_required' => 5000,
                'bet_bonus' => 500,
                'bet_symbol' => 'VIP4',
                'required_achievements' => 4
            ],
            [
                'bet_level' => 5,
                'title' => 'Platina',
                'description' => 'Status VIP Platina com benefícios premium',
                'bet_required' => 10000,
                'bet_bonus' => 1000,
                'bet_symbol' => 'VIP5',
                'required_achievements' => 5
            ],
            [
                'bet_level' => 6,
                'title' => 'Diamante',
                'description' => 'Status VIP Diamante com máximos benefícios',
                'bet_required' => 20000,
                'bet_bonus' => 2000,
                'bet_symbol' => 'VIP6',
                'required_achievements' => 6
            ],
            [
                'bet_level' => 7,
                'title' => 'Mestre',
                'description' => 'Status VIP Mestre com privilégios únicos',
                'bet_required' => 35000,
                'bet_bonus' => 3500,
                'bet_symbol' => 'VIP7',
                'required_achievements' => 7
            ],
            [
                'bet_level' => 8,
                'title' => 'Lendário',
                'description' => 'Status VIP Lendário - o topo da hierarquia',
                'bet_required' => 50000,
                'bet_bonus' => 5000,
                'bet_symbol' => 'VIP8',
                'required_achievements' => 8
            ],
            [
                'bet_level' => 9,
                'title' => 'Supremo',
                'description' => 'Status VIP Supremo com benefícios ilimitados',
                'bet_required' => 75000,
                'bet_bonus' => 7500,
                'bet_symbol' => 'VIP9',
                'required_achievements' => 9
            ],
            [
                'bet_level' => 10,
                'title' => 'Imperador',
                'description' => 'Status VIP Imperador - domínio absoluto',
                'bet_required' => 100000,
                'bet_bonus' => 10000,
                'bet_symbol' => 'VIP10',
                'required_achievements' => 10
            ],
            [
                'bet_level' => 11,
                'title' => 'Divino',
                'description' => 'Status VIP Divino com poderes sobrenaturais',
                'bet_required' => 150000,
                'bet_bonus' => 15000,
                'bet_symbol' => 'VIP11',
                'required_achievements' => 11
            ],
            [
                'bet_level' => 12,
                'title' => 'Celestial',
                'description' => 'Status VIP Celestial - ascensão aos céus',
                'bet_required' => 200000,
                'bet_bonus' => 20000,
                'bet_symbol' => 'VIP12',
                'required_achievements' => 12
            ],
            [
                'bet_level' => 13,
                'title' => 'Místico',
                'description' => 'Status VIP Místico com magia e mistério',
                'bet_required' => 300000,
                'bet_bonus' => 30000,
                'bet_symbol' => 'VIP13',
                'required_achievements' => 13
            ],
            [
                'bet_level' => 14,
                'title' => 'Transcendental',
                'description' => 'Status VIP Transcendental - além dos limites',
                'bet_required' => 400000,
                'bet_bonus' => 40000,
                'bet_symbol' => 'VIP14',
                'required_achievements' => 14
            ],
            [
                'bet_level' => 15,
                'title' => 'Infinito',
                'description' => 'Status VIP Infinito - recompensas sem fim',
                'bet_required' => 500000,
                'bet_bonus' => 50000,
                'bet_symbol' => 'VIP15',
                'required_achievements' => 15
            ],
            [
                'bet_level' => 16,
                'title' => 'Absoluto',
                'description' => 'Status VIP Absoluto - controle total',
                'bet_required' => 750000,
                'bet_bonus' => 75000,
                'bet_symbol' => 'VIP16',
                'required_achievements' => 16
            ],
            [
                'bet_level' => 17,
                'title' => 'Supremo',
                'description' => 'Status VIP Supremo - o ápice do poder',
                'bet_required' => 1000000,
                'bet_bonus' => 100000,
                'bet_symbol' => 'VIP17',
                'required_achievements' => 17
            ],
            [
                'bet_level' => 18,
                'title' => 'Divino',
                'description' => 'Status VIP Divino Supremo',
                'bet_required' => 1500000,
                'bet_bonus' => 150000,
                'bet_symbol' => 'VIP18',
                'required_achievements' => 18
            ],
            [
                'bet_level' => 19,
                'title' => 'Lendário',
                'description' => 'Status VIP Lendário - lenda viva',
                'bet_required' => 2000000,
                'bet_bonus' => 200000,
                'bet_symbol' => 'VIP19',
                'required_achievements' => 19
            ],
            [
                'bet_level' => 20,
                'title' => 'Épico',
                'description' => 'Status VIP Épico - o nível mais alto possível!',
                'bet_required' => 5000000,
                'bet_bonus' => 500000,
                'bet_symbol' => 'VIP20',
                'required_achievements' => 20
            ]
        ];

        // Adiciona os novos níveis VIP
        foreach ($vipLevels as $level) {
            // Verifica se o nível já existe
            $existingVip = Vip::where('bet_level', $level['bet_level'])->first();
            
            if (!$existingVip) {
                Vip::create($level);
                $this->command->info("Nível VIP {$level['bet_level']} - {$level['title']} criado com sucesso!");
            } else {
                $this->command->info("Nível VIP {$level['bet_level']} já existe, pulando...");
            }
        }

        $this->command->info('Seeder de níveis VIP concluído!');
    }
} 