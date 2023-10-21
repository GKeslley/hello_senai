<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable implements JWTSubject
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
    protected $primaryKey = "apelido";
    const CREATED_AT = 'data_criacao';
    const UPDATED_AT = 'data_atualizacao';
    /**
     * Função para criação do usuario;
     * @param $data - 
    */
    public function createUser($data) {
        $data['data_criacao'] = \Carbon\Carbon::now();
        $id = $this->insertGetId($data);
        $this->generateUsername($data['nome'], $id);
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
