<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V1\ProjectResource;

class Invitation extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'titulo',
        'descricao',
        'data_convite',
        'idusuario',
        'slug'
    ];
    
    protected $table = "convite";
    protected $primaryKey = "idconvite";
    const UPDATED_AT = "data_atualizado";
    
    public function user()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    /**
     * Método para criar projeto
     * @param [array] $data
     * @return int $idprojeto
     */
    public function createInvitation($data) { 
        if ($id = $this->insertGetId($data)) return $id;
        return false;
    }

    public function updateInvitation($data)
    {
        $idInvitation = $data['idconvite'];
        if ($this->where('idconvite', '=', $idInvitation)->update($data))
        {
            return true;
        };
        return false;
    }

    public function deleteInvitation($idInvitation)
    {
        if ($this->where('idconvite', '=', $idInvitation)->delete())
        {
            return true;
        };
        return false;
    }
}