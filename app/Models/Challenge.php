<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Support\Facades\DB;
use App\Models\Invitation;

class Challenge extends Invitation
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = "idconvite";

    public function createChallenge($data, $teacherId)
    {
        $idInvite = parent::createInvitation($data);
        $dataChallenge = [
            'idprofessor' => $teacherId,
            'idconvite' => $idInvite,
            'imagem' => $data['imagem'] ?: null
        ];
        if (DB::table('desafio')->insert($dataChallenge)) return true;
        return false;
    }

    public function updateChallenge($idprofessor, $idconvite, $data) {
        DB::table('desafio as d')
        ->join('convite as c','d.idconvite','=','c.idconvite')
        ->where('idprofessor','=',$idprofessor,'and','idconvite', '=', $idconvite)
        ->update($data);
    }
}
