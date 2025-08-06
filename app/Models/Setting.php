<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'software_name',
        'software_description',
        'software_notification',
        'software_favicon',
        'software_logo_white',
        'software_logo_black',
        'software_logo_black2',
        'software_background',
        'currency_code',
        'decimal_format',
        'currency_position',
        'prefix',
        'storage',
        'min_deposit',
        'max_deposit',
        'min_withdrawal',
        'max_withdrawal',
        'bonus_vip',
        'activate_vip_bonus',
        'ngr_percent',
        'revshare_percentage',
        'revshare_reverse',
        'cpa_value',
        'cpa_baseline',
        'soccer_percentage',
        'turn_on_football',
        'initial_bonus',
        'bspay_is_enable',      
        'ondapay_is_enable',
        'withdrawal_limit',
        'withdrawal_period',
        'disable_spin',
        'perc_sub_lv1',
        'perc_sub_lv2',
        'perc_sub_lv3',
        'rollover',
        'rollover_deposit',
        'disable_rollover',
        'rollover_protection',
        'default_gateway',
        'img_modal_pop',
        'modal_pop_up',
        'modal_active',
        'deposit_min_saque',
        'disable_deposit_min',
        'saldo_ini',
        'rollover_cadastro',
        'disable_rollover_cadastro',
        'key',
        'value'
    ];

    protected $casts = [
        'value' => 'string',
    ];

    protected $hidden = array('updated_at');

    /**
     * Obtém uma configuração pelo nome
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Define uma configuração
     */
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
