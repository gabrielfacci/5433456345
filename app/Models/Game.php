<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    use HasFactory;

    /**
     * A tabela do banco de dados usada pelo modelo.
     *
     * @var string
     */
    protected $table = 'games';

    /**
     * A chave primária associada à tabela.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id',
        'game_server_url',
        'game_id',
        'game_name',
        'game_code',
        'game_type',
        'description',
        'cover',
        'status',
        'technology',
        'has_lobby',
        'is_mobile',
        'has_freespins',
        'has_tables',
        'only_demo',
        'rtp',
        'distribution',
        'views',
        'is_featured',
        'featured_section_1',
        'featured_section_2',
        'featured_section_3',
        'featured_section_4',
        'featured_section_5',
        'show_home',
        'original'
    ];

    /**
     * Atributos que são castados.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'original' => 'boolean',
        'show_home' => 'boolean',
        'featured_section_1' => 'boolean',
        'featured_section_2' => 'boolean',
        'featured_section_3' => 'boolean',
        'featured_section_4' => 'boolean',
        'featured_section_5' => 'boolean',
    ];

    /**
     * Relacionamento com a tabela providers.
     *
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    /**
     * Relacionamento com a tabela categories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Relacionamento com a tabela game_likes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_likes', 'game_id', 'user_id');
    }

    /**
     * Verifica se o usuário atual deu like no jogo.
     *
     * @return bool
     */
    public function hasLike(): bool
    {
        if (!auth('api')->check()) {
            return false;
        }
        
        return $this->likes()->where('user_id', auth('api')->id())->exists();
    }

    /**
     * Verifica se um usuário específico deu like no jogo.
     *
     * @param int $userId
     * @return bool
     */
    public function hasLikeByUser(int $userId): bool
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Retorna o total de likes do jogo.
     *
     * @return int
     */
    public function getTotalLikesAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * Retorna a data de criação formatada.
     *
     * @return string
     */
    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('Y-m-d');
    }

    /**
     * Retorna a data de criação de forma legível.
     *
     * @return string
     */
    public function getDateHumanReadableAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
}
