<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Admin\Pages\AdvancedPage;
use App\Filament\Admin\Pages\DashboardAdmin;
use App\Filament\Admin\Pages\DigitoPayPaymentPage;
use App\Filament\Admin\Pages\GatewayPage;
use App\Filament\Admin\Pages\LayoutCssCustom;
use App\Filament\Admin\Pages\SuitPayPaymentPage;
use App\Filament\Admin\Pages\TwoFactorAuthPage;
use App\Filament\Admin\Resources\AffiliateWithdrawResource;
use App\Filament\Admin\Resources\BannerResource;
use App\Filament\Admin\Resources\CategoryResource;
use App\Filament\Admin\Resources\DepositResource;
use App\Filament\Admin\Resources\GameResource;
use App\Filament\Admin\Resources\MissionResource;
use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Resources\ProviderResource;
use App\Filament\Admin\Resources\SettingResource;
use App\Filament\Admin\Resources\UserResource;
use App\Filament\Admin\Resources\VipResource;
use App\Filament\Admin\Resources\WalletResource;
use App\Filament\Admin\Resources\WithdrawalResource;
use App\Livewire\AdminWidgets;
use App\Livewire\WalletOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Admin\Resources\ReportResource;

                                        // NOVAS FUNÇOES 

use App\Filament\Admin\Pages\RoundsFreePage;
use App\Filament\Admin\Resources\GameOpenConfigResource;
use App\Filament\Admin\Resources\CupomResource;
use App\Filament\Admin\Resources\PromotionResource;
use App\Filament\Admin\Resources\DistributionSystemResource;
use App\Filament\Admin\Resources\DailyBonusConfigResource;
use App\Filament\Admin\Resources\GiftResource;
use App\Filament\Admin\Resources\CrmSignupResource;
use App\Filament\Admin\Resources\CrmDepositUserResource;
use App\Filament\Admin\Resources\SystemNotificationResource;
use App\Filament\Admin\Resources\AffiliateInfoResource;
use App\Filament\Admin\Resources\MissionDepositResource;
use App\Filament\Admin\Resources\VipRewardResource;
use App\Filament\Admin\Resources\AchievementResource;
use App\Filament\Admin\Pages\SportsbookPage;
use App\Filament\Admin\Resources\CashbackSettingResource;
use App\Filament\Admin\Resources\RaspadinhaResource;


class AdminPanelProvider extends PanelProvider
{
    /**
     * @param Panel $panel
     * @return Panel
     */

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('stv')
            ->path('stv')
            ->login()
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::Orange,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])

            ->font('Roboto Condensed')
            ->brandLogo(fn () => view('filament.components.logo'))
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                DashboardAdmin::class,
                TwoFactorAuthPage::class,
                SportsbookPage::class,
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->collapsibleNavigationGroups(true)
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                WalletOverview::class,
                AdminWidgets::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    auth()->user()->hasRole('admin') ?
                        NavigationGroup::make()
                        ->items([
                            NavigationItem::make('dashboard')
                                ->icon('heroicon-o-home')
                                ->label(fn (): string => __('filament-panels::pages/dashboard.title'))
                                ->url(fn (): string => DashboardAdmin::getUrl())
                                ->isActiveWhen(fn () => request()->routeIs('filament.mts22.pages.dashboard-admin')),

                            NavigationItem::make('settings')
                                ->icon('heroicon-o-cog-6-tooth')
                                ->label(fn (): string => 'Configurações')
                                ->url(fn (): string => SettingResource::getUrl())
                                ->isActiveWhen(fn () => request()->routeIs('filament.mts22.resources.settings.*'))
                                ->visible(fn(): bool => auth()->user()->hasRole('admin')),
  


                        ]) : NavigationGroup::make()
                    ,
                

                          NavigationGroup::make('EXCLUSIVO QIC BUSINESS')
                              ->items([

                                  ...RaspadinhaResource::getNavigationItems(),
                              ]),

                   
                    auth()->user()->hasRole('admin') ?
                        NavigationGroup::make('Pagamentos')
                            ->items([
                                NavigationItem::make('gateway')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->label(fn (): string => 'Gateway de Pagamentos')
                                    ->url(fn (): string => GatewayPage::getUrl())
                                    ->isActiveWhen(fn () => request()->routeIs('filament.mts22.pages.gateway-page'))
                                    ->visible(fn(): bool => auth()->user()->hasRole('admin')),
                            ])
                        : NavigationGroup::make()
                    ,
                    auth()->user()->hasRole('admin') ?
                        NavigationGroup::make('Customização')
                            ->items([
                                ...BannerResource::getNavigationItems(),

                                NavigationItem::make('custom-layout')
                                    ->icon('heroicon-o-paint-brush')
                                    ->label(fn (): string => 'Customização')
                                    ->url(fn (): string => LayoutCssCustom::getUrl())
                                    ->isActiveWhen(fn () => request()->routeIs('filament.mts22.pages.layout-css-custom'))
                                    ->visible(fn(): bool => auth()->user()->hasRole('admin'))
                            ])
                        : NavigationGroup::make()
                    ,
                    auth()->user()->hasRole('admin') ?
                        NavigationGroup::make('Administração')
                            ->items([
                                ...UserResource::getNavigationItems(),
                                ...WalletResource::getNavigationItems(),
                                ...DepositResource::getNavigationItems(),
                                ...WithdrawalResource::getNavigationItems(),
                                NavigationItem::make('withdraw_affiliates')
                                ->icon('heroicon-o-banknotes')
                                ->label(fn (): string => 'Saques de Afiliados')
                                ->url(fn (): string => AffiliateWithdrawResource::getUrl())
                                ->badge(fn (): string => \App\Models\AffiliateWithdraw::where('status', 0)->count())
                                ->isActiveWhen(fn () => request()->routeIs('filament.mts22.resources.affiliate-withdraws.*'))
                                ->visible(fn(): bool => auth()->user()->hasRole('admin')),
                            ])
                        : NavigationGroup::make()
                    ,

                 
                    NavigationGroup::make('maintenance')
                        ->label('Manutenção')
                        ->items([
                            NavigationItem::make('Limpar o cache')
                                ->url(url('/clear'), shouldOpenInNewTab: false)
                                ->icon('heroicon-o-trash')
                        ])
                    ,
                ]);
            })
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\CheckAdminRole::class,
                \App\Http\Middleware\TwoFactorAuthMiddleware::class,
                \App\Http\Middleware\ClearTwoFactorSession::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make())
            ;
    }
}
