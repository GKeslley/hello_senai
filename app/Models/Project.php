<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V1\ProjectResource;

class Project extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'idusuario',
        'descricao',
        'nome_projeto',
        'status',
        'slug'
    ];

    //Variaveis de definição da tabela
    protected $table = "projeto";
    protected $primaryKey = "idprojeto";
    const UPDATED_AT = "data_atualizado";

    /**
     * Método para realizar relacionamento com o usuario
     * @return array $data
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    /**
     * Método para criar projeto
     * @param [array] $data
     * @return int $idprojeto
     */
    public function createProject($data) { 
        if ($id = $this->insertGetId($data)) return $id;
        return false;
    }

    public function updateProject($data)
    {
        $idProject = $data['idprojeto'];
        if ($this->where('idprojeto', '=', $idProject)->update($data))
        {
            return true;
        };
        return false;
    }

    public function deleteProject($idProject)
    {
        if ($this->where('idprojeto', '=', $idProject)->delete())
        {
            return true;
        };
        return false;
    }

    public function getProjectBySlug($slug)
    {
        if ($projectId = $this->where('slug', '=', $slug)->value('idprojeto'))
        {
            return $projectId;
        };
        return null;
    }

    public function getUserPermissionInProject($permission)
    {
        if ($permissionId = DB::table('permissao')
        ->where('tipo', '=', $permission)->value('idpermissao'))
        {
            return $permissionId;
        };
        return null;
    }

    public function addRangeParticipants($participants)
    {
        if (DB::table('acesso')->insert($participants)) return true;
        return false;
    }
}