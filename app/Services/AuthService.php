<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuthService
{

  public function isAdm(int $userId)
  {
        $userRule = DB::table('adm')->where('idusuario', $userId)->exists();
        if ($userRule) return true;
        return false;
  }

    public function isRedator(int $userId)
    {
        $userRule = DB::table('acesso')->where('idusuario', $userId)->value('permissao');
        $ruleValue = DB::table('permissao')->where('idpermissao', $userRule)->value('tipo');
        if ($ruleValue == 'editor') return true;
        return false;
    }

    public function isOwnerOfInvite(int $userId)
    {
        $userRule = DB::table('convite')->where('idusuario', $userId)->exists();
        if ($userRule && $userRule == 'owner') return true;
        return false;
    }

    public function isTeacher(int $userId)
    {
        $userRule = DB::table('professor')->where('idusuario', $userId)->exists();
        if ($userRule) return true;
        return false;
    }
}