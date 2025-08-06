<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DistributionSystemResource\Pages;
use App\Models\DistributionSystem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class DistributionSystemResource extends Resource
{
    /**
     * Modelo Eloquent que este recurso gerencia
     */
    protected static ?string $model = DistributionSystem::class;

    /**
     * Customizações de exibição no menu do Filament
     */
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $label = 'Distribuição de Ganhos';
    protected static ?string $pluralLabel = 'Sistema de Distribuição';
    protected static ?string $navigationLabel = 'Configuração de Distribuição';
    protected static ?string $navigationGroup = 'Finanças';

    /**
     * Controla o acesso: somente Admin pode ver
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Definição do formulário para edição do registro
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // Seção Principal
                Forms\Components\Section::make('Configuração Principal')
                    ->schema([
                        Forms\Components\Toggle::make('ativo')
                            ->label('Sistema Ativado')
                            ->helperText('Ligue ou desligue o sistema de distribuição.')
                            ->live(),

                        Forms\Components\TextInput::make('meta_arrecadacao')
                            ->label('Meta de Arrecadação (R$)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('R$')
                            ->required(),

                        Forms\Components\TextInput::make('percentual_distribuicao')
                            ->label('Percentual de Distribuição (%)')
                            ->numeric()
                            ->step(1)
                            ->suffix('%')
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(),
                    ])->columns(3),

                // Seção RTP
                Forms\Components\Section::make('Configuração de RTP')
                    ->schema([
                        Forms\Components\TextInput::make('rtp_arrecadacao')
                            ->label('RTP Arrecadação (%)')
                            ->helperText('RTP mais baixo para arrecadar mais')
                            ->numeric()
                            ->step(1)
                            ->suffix('%')
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(),

                        Forms\Components\TextInput::make('rtp_distribuicao')
                            ->label('RTP Distribuição (%)')
                            ->helperText('RTP mais alto para distribuir aos jogadores')
                            ->numeric()
                            ->step(1)
                            ->suffix('%')
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(),
                    ])->columns(2),

                // Seção Status Atual
                Forms\Components\Section::make('Status Atual - Atualizando Automaticamente')
                    ->description('Os dados abaixo são atualizados automaticamente em tempo real')
                    ->schema([
                        // Coluna 1: Dados Básicos
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('modo')
                                ->label('Modo Atual')
                                ->options([
                                    'arrecadacao' => '💰 Arrecadação',
                                    'distribuicao' => '🎁 Distribuição',
                                ])
                                ->disabled()
                                ->afterStateHydrated(function ($state, $record) {
                                    if ($record) {
                                        static::verificarEAtualizarModo($record);
                                    }
                                }),

                            Forms\Components\TextInput::make('start_cycle_at')
                                ->label('Início do Ciclo')
                                ->disabled()
                                ->formatStateUsing(function ($state) {
                                    if (!$state) return 'Não definido';
                                    return \Carbon\Carbon::parse($state)
                                        ->setTimezone('America/Sao_Paulo')
                                        ->format('d/m/Y H:i:s');
                                }),
                        ])->columnSpan(1),

                        // Coluna 2: Valores Financeiros
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('total_arrecadado')
                                ->label('Total Arrecadado (R$)')
                                ->prefix('R$')
                                ->disabled()
                                ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2, ',', '.')),

                            Forms\Components\TextInput::make('total_distribuido')
                                ->label('Total Distribuído (R$)')
                                ->prefix('R$')
                                ->disabled()
                                ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2, ',', '.')),
                        ])->columnSpan(1),

                        // Coluna 3: Progresso
                        Forms\Components\Group::make([
                            Forms\Components\Placeholder::make('progresso')
                                ->label('Progresso do Ciclo')
                                ->content(function ($record) {
                                    if (!$record) return 'Sistema não configurado';
                                    
                                    if ($record->modo === 'arrecadacao') {
                                        $totalBets = \App\Models\Order::where('type', 'bet')
                                            ->where('created_at', '>=', $record->start_cycle_at)
                                            ->sum('amount');
                                        $progress = $record->meta_arrecadacao > 0 
                                            ? ($totalBets / $record->meta_arrecadacao) * 100 
                                            : 0;
                                        return "📈 Arrecadação: " . number_format($progress, 1) . "% da meta";
                                    } else {
                                        $totalWins = \App\Models\Order::where('type', 'win')
                                            ->where('created_at', '>=', $record->start_cycle_at)
                                            ->sum('amount');
                                        $valorDistribuir = $record->meta_arrecadacao * ($record->percentual_distribuicao / 100);
                                        $progress = $valorDistribuir > 0 
                                            ? ($totalWins / $valorDistribuir) * 100 
                                            : 0;
                                        return "📈 Distribuição: " . number_format($progress, 1) . "% da meta";
                                    }
                                }),

                            Forms\Components\Placeholder::make('valor_distribuir')
                                ->label('Valor a Distribuir')
                                ->content(function ($record) {
                                    if (!$record) return 'R$ 0,00';
                                    $valor = $record->meta_arrecadacao * ($record->percentual_distribuicao / 100);
                                    return 'R$ ' . number_format($valor, 2, ',', '.');
                                }),
                        ])->columnSpan(1),

                        // Coluna 4: Status do Sistema
                        Forms\Components\Group::make([
                            Forms\Components\Placeholder::make('status_automatico')
                                ->label('Status do Sistema')
                                ->content(function ($record) {
                                    $status = '<div style="display: flex; flex-direction: column; gap: 4px;">';
                                    $status .= '<div>🤖 <strong>Sistema automático ATIVO</strong></div>';
                                    $status .= '<div style="font-size: 12px; color: #10b981;">✅ Verificação automática ao carregar</div>';
                                    $status .= '<div style="font-size: 12px; color: #10b981;">✅ Mudança automática de modos</div>';
                                    $status .= '<div style="font-size: 12px; color: #10b981;">✅ Atualização automática do RTP</div>';
                                    $status .= '<div style="font-size: 12px; color: #10b981;">✅ Cron executando a cada minuto</div>';
                                    $status .= '</div>';
                                    return new \Illuminate\Support\HtmlString($status);
                                }),

                            Forms\Components\Placeholder::make('ultima_atualizacao')
                                ->label('Última Atualização')
                                ->content(function ($record) {
                                    if (!$record?->start_cycle_at) {
                                        return '🕐 Nunca';
                                    }
                                    
                                    $lastUpdate = \Carbon\Carbon::parse($record->start_cycle_at)
                                        ->setTimezone('America/Sao_Paulo')
                                        ->format('d/m/Y H:i:s');
                                    $ago = \Carbon\Carbon::parse($record->start_cycle_at)
                                        ->setTimezone('America/Sao_Paulo')
                                        ->diffForHumans();
                                    return '🕐 ' . $lastUpdate . ' (' . $ago . ')';
                                }),
                        ])->columnSpan(1),
                    ])->columns(4),

                // Seção Ações
                Forms\Components\Section::make('Ações do Sistema')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('processar_agora')
                            ->label('🔄 Atualizar Processo')
                            ->color('primary')
                            ->action(function () {
                                $distribution = \App\Models\DistributionSystem::first();
                        
                                if (!$distribution || !$distribution->ativo) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Sistema inativo!')
                                        ->warning()
                                        ->send();
                                    return;
                                }
                        
                                if (!$distribution->start_cycle_at) {
                                    $distribution->update(['start_cycle_at' => now()]);
                                }
                        
                                if ($distribution->modo === 'arrecadacao') {
                                    $totalBets = \App\Models\Order::where('type', 'bet')
                                        ->where('created_at', '>=', $distribution->start_cycle_at)
                                        ->sum('amount');
                        
                                    $distribution->total_arrecadado = $totalBets;
                                    $distribution->save();
                        
                                    if ($totalBets >= $distribution->meta_arrecadacao) {
                                        \Log::info('🎉 META ATINGIDA! Mudando para distribuição');
                        
                                        $distribution->update([
                                            'modo' => 'distribuicao',
                                            'start_cycle_at' => now(),
                                        ]);
                        
                                        static::atualizarRTP($distribution->rtp_distribuicao);
                        
                                        \Filament\Notifications\Notification::make()
                                            ->title('🎉 Meta atingida!')
                                            ->body('Sistema mudou para modo DISTRIBUIÇÃO')
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Processo atualizado!')
                                            ->body('Modo: ARRECADAÇÃO - R$ ' . number_format($totalBets, 2, ',', '.') . ' de R$ ' . number_format($distribution->meta_arrecadacao, 2, ',', '.'))
                                            ->success()
                                            ->send();
                                    }
                                } elseif ($distribution->modo === 'distribuicao') {
                                    $totalWins = \App\Models\Order::where('type', 'win')
                                        ->where('created_at', '>=', $distribution->start_cycle_at)
                                        ->sum('amount');
                        
                                    $distribution->total_distribuido = $totalWins;
                                    $distribution->save();
                        
                                    $valorDistribuir = $distribution->meta_arrecadacao * ($distribution->percentual_distribuicao / 100);
                                    $tempoMinimoEmSegundos = 600; // Tempo mínimo exigido na distribuição (10 minutos)
                                    $tempoDecorrido = now()->diffInSeconds($distribution->start_cycle_at);
                        
                                    if ($totalWins >= $valorDistribuir) {
                                        \Log::info('🎉 DISTRIBUIÇÃO COMPLETA! Resetando ciclo');
                        
                                        $distribution->update([
                                            'modo' => 'arrecadacao',
                                            'start_cycle_at' => now(),
                                            'total_arrecadado' => 0,
                                            'total_distribuido' => 0,
                                        ]);
                        
                                        static::atualizarRTP($distribution->rtp_arrecadacao);
                        
                                        \Filament\Notifications\Notification::make()
                                            ->title('🎉 Distribuição completa!')
                                            ->body('Ciclo resetado - Sistema voltou para ARRECADAÇÃO')
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Sistema processado!')
                                            ->body('Modo: DISTRIBUIÇÃO - R$ ' . number_format($totalWins, 2, ',', '.') . ' de R$ ' . number_format($valorDistribuir, 2, ',', '.'))
                                            ->send();
                                    }
                                }
                            }),
                        ]),
                    ])
            ]);
    }

    public static function verificarEAtualizarModo($record)
    {
        // Força atualização dos dados do registro
        $record->refresh();
        
        if ($record->modo === 'arrecadacao') {
            $totalBets = \App\Models\Order::where('type', 'bet')
                ->where('created_at', '>=', $record->start_cycle_at)
                ->sum('amount');
            
            if ($totalBets >= $record->meta_arrecadacao) {
                // Mudar para distribuição
                $record->update([
                    'modo' => 'distribuicao',
                    'start_cycle_at' => now(),
                ]);
                
                // Atualizar RTP
                static::atualizarRTP($record->rtp_distribuicao);
                
                \Log::info('🎉 ARRECADAÇÃO COMPLETA! Mudou para DISTRIBUIÇÃO', [
                    'total_arrecadado' => $totalBets,
                    'meta' => $record->meta_arrecadacao
                ]);
            }
        } elseif ($record->modo === 'distribuicao') {
            $totalWins = \App\Models\Order::where('type', 'win')
                ->where('created_at', '>=', $record->start_cycle_at)
                ->sum('amount');
        
            $valorDistribuir = $record->meta_arrecadacao * ($record->percentual_distribuicao / 100);
        
                                        $tempoMinimoEmSegundos = 600; // mínimo 10 minutos no modo distribuição
            $tempoDecorrido = now()->diffInSeconds($record->start_cycle_at);
        
            if ($totalWins >= $valorDistribuir) {
            
                // Mudar para arrecadação
                $record->update([
                    'modo' => 'arrecadacao',
                    'start_cycle_at' => now(),
                    'total_arrecadado' => 0,
                    'total_distribuido' => 0,
                ]);
        
                static::atualizarRTP($record->rtp_arrecadacao);
        
                \Log::info('🎉 DISTRIBUIÇÃO COMPLETA! Mudou para ARRECADAÇÃO', [
                    'total_distribuido' => $totalWins,
                    'meta' => $valorDistribuir,
                ]);
            }
        }
    }

    public static function atualizarRTP($rtp)
    {
        $setting = \App\Models\GamesKey::first();
        if ($setting) {
            try {
                $response = \Illuminate\Support\Facades\Http::withOptions(['force_ip_resolve' => 'v4'])
                    ->put('https://api.playfivers.com/api/v2/agent', [
                        'agentToken' => $setting->playfiver_token,
                        'secretKey' => $setting->playfiver_secret,
                        'rtp' => $rtp,
                        'bonus_enable' => true,
                    ]);
                
                \Log::info('RTP Atualizado via Filament', [
                    'rtp' => $rtp,
                    'response' => $response->json()
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao atualizar RTP via Filament', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Opcional: Tabela de visualização (caso queira ver o registro),
     * mas sem permitir criação ou exclusão.
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('ativo')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Ativado' : 'Desativado')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('meta_arrecadacao')
                    ->label('Meta de Arrecadação'),

                Tables\Columns\TextColumn::make('percentual_distribuicao')
                    ->label('% de Distribuição'),

                Tables\Columns\TextColumn::make('rtp_arrecadacao')
                    ->label('RTP de Arrecadação'),

                Tables\Columns\TextColumn::make('rtp_distribuicao')
                    ->label('RTP de Distribuição'),

                Tables\Columns\TextColumn::make('total_arrecadado')
                    ->label('Total Arrecadado'),

                Tables\Columns\TextColumn::make('total_distribuido')
                    ->label('Total Distribuído'),

                Tables\Columns\BadgeColumn::make('modo')
                    ->label('Modo Atual')
                    ->formatStateUsing(fn ($state) => $state === 'arrecadacao' ? 'Arrecadação' : 'Distribuição')
                    ->color(fn ($state) => $state === 'arrecadacao' ? 'primary' : 'success'),
            ])
            ->actions([
                // Só permitimos edição do único registro
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]) // sem ações em lote
            ->defaultPagination(1); // exibe no máximo 1 registro
    }

    /**
     * Importante: forçar a query a retornar somente um registro.
     */
    public static function getEloquentQuery(): Builder
    {
        // limit(1) para não listar múltiplos
        return parent::getEloquentQuery()->limit(1);
    }

    /**
     * Redefine as páginas disponíveis:
     *  - 'index' => redirecionado para a tela de edição
     */
    public static function getPages(): array
    {
        return [
            // Ao acessar /distribution-systems, irá diretamente para EditDistributionSystem
            'index' => Pages\EditDistributionSystem::route('/'),
        ];
    }
}
