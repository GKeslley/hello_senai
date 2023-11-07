<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Invitation;
use Symfony\Component\HttpKernel\Exception\HttpException;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $fillable = [
        'nome',
        'email',
        'senha'
    ];
    protected $hidden = [
        'senha'
    ];
    protected $table = "usuario";
    protected $primaryKey = "idusuario";
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    /**
     * Função para criação do usuario;
     * @param $data - 
    */

    public function project()
    {
        return $this->hasMany(Project::class);
    }

    public function invite()
    {
        return $this->hasMany(Invitation::class);
    }

    public function createUser($data) {
        $data['data_criacao'] = \Carbon\Carbon::now();
        $id = $this->insertGetId($data);
        $this->generateUsername($data['nome'], $id);
        return $id;
    }

    public function createAdm($data) {
        $idUser = $this->createUser($data);
        if (!DB::table('adm')->insert(['idusuario' => $idUser]))
        {
            throw new HttpException(401, 'Erro na ação');
        };
    }
    
    /**
     * Função de atualização do Usuario;
     * @param $data  dados do usuario
     * @param $id  id do usuario
     * @return void
     */
    public function updateUser($data,$id) {
        return $this->where('idsuario',$id)->update($data);
    }

    /**
     * Função para deletar o usuario;
     * @param $id id do usuario
     * @return void
     */
    public function deleteUser($id) {
        return $this->where('idusuario', $id)->delete();
    }

    /**
     * Função para gerar um apelido para o usuario;
     * @param $name nome do usuario
     * @param $user_id id do usuario
     * @return void
     */
    public function generateUsername($name, $user_id) {
        $username = $name . substr(sha1($name . $user_id), 0, 8);
        $value = ['apelido' => $username];
        DB::table('usuario')->where('idusuario', $user_id)->update($value);
    }

    /**
     * Função para desativar a conta do usuario;
     * @param $id id do usuario
     * @return void
     */
    public function desativateUser($id) {
        return $this->where('idusuario',$id)->update(['status' => 'inativo']);
    }
    
    /**
     * Função para selecionar o usuario pelo apelido;
     * @param $nickname apelido do usuario
     * @return array
     */
    public function getByNickname($nickname) {
        return $this->where('apelido', $nickname)->get()->toArray();
    }

    /**
     * Função para selecionar todos os usuario;
     * @return array
     */
    public function getAll() {
        return $this->get()->toArray();
    }

    public function getAuthPassword() {
        return $this->senha;
    }
}