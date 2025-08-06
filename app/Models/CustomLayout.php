<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomLayout extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'custom_layouts';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'font_family_default',
        'primary_color',
        'primary_opacity_color',
        'secundary_color',
        'gray_dark_color',
        'gray_light_color',
        'gray_medium_color',
        'gray_over_color',
        'title_color',
        'text_color',
        'sub_text_color',
        'placeholder_color',
        'background_color',

        'background_base',
        'background_base_dark',

        'input_primary',
        'input_primary_dark',

        'carousel_banners',
        'carousel_banners_dark',

        'sidebar_color',
        'sidebar_color_dark',

        'navtop_color',
        'navtop_color_dark',

        'side_menu',
        'side_menu_dark',

        'footer_color',
        'footer_color_dark',

        'card_color',
        'card_color_dark',

        'border_radius',
        'custom_css',
        'custom_js',
        'custom_header',
        'custom_body',

        /// redes sociais
        'instagram',
        'facebook',
        'telegram',
        'twitter',
        'whastapp',
        'youtube',
        'Suporte',
        'esportes',
        'apostasaovivo',
        'cassino',
        'cassinoaovivo',
        'ajuda',
        'live_ganhos_status',
        'mascote_ganhos',
        'ultimos_ganhos_player_color',
        'ultimos_ganhos_valor_color',
        
        // Feedback page
        'feedback_page_background',
        'feedback_page_text_color',
        'feedback_page_title_color',
        'feedback_page_button_color',
        'feedback_page_button_text_color',
        'feedback_page_fade_color',
        'feedback_page_page_background',
        'feedback_page_form_background',
        'feedback_share_title_color',
        'feedback_share_background_color',
        'feedback_page_form_text_color',
        'feedback_page_form_button_color',
        'feedback_page_form_button_text_color',
        'ultimos_ganhos_titulo_color',
        'ultimos_ganhos_subtitulo_color',
        'ultimos_ganhos_fade_color',
        'ultimos_ganhos_background_color',
        'ultimos_ganhos_titulo_texto',
        'ultimos_ganhos_subtitulo_texto',
        'banner_deposito1',

        // Mascote do Sidebar
        'sidebar_mascote',
        'sidebar_mascote_titulo',
        'sidebar_mascote_subtitulo',
        'sidebar_mascote_titulo_color',
        'sidebar_mascote_subtitulo_color',
        'sidebar_mascote_background',

        // Resgatar Código
        'sidebar_codigo_imagem',
        'sidebar_codigo_titulo',
        'sidebar_codigo_subtitulo',
        'sidebar_codigo_titulo_color',
        'sidebar_codigo_subtitulo_color',
        'sidebar_codigo_background',

        // Missão
        'sidebar_missao_imagem',
        'sidebar_missao_titulo',
        'sidebar_missao_subtitulo',
        'sidebar_missao_titulo_color',
        'sidebar_missao_subtitulo_color',
        'sidebar_missao_background',

        // Promoções
        'sidebar_promocoes_imagem',
        'sidebar_promocoes_titulo',
        'sidebar_promocoes_subtitulo',
        'sidebar_promocoes_titulo_color',
        'sidebar_promocoes_subtitulo_color',
        'sidebar_promocoes_background',

        // Links do Sidebar
        'sidebar_mascote_link',
        'sidebar_codigo_link',
        'sidebar_missao_link',
        'sidebar_promocoes_link',
    ];

}
