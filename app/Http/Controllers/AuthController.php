<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\AuthService;
use App\Http\Resources\V1\UserResource;
use App\Http\Requests\StoreTeacherRequest;

class AuthController extends Controller
{
    private $service;
    
    
    public function __construct(
        protected User $repository
    )
    {      
        $this->service = new AuthService();
    }

    public function getAbilities()
    {
        return ['project-store', 'project-update', 'project-destroy', 'invite-store', 'invite-update', 'invite-destroy'];
    }

    public function register(Request $request)
    {

    }

    public function registerTeacher(StoreTeacherRequest $request)
    {
        if (!Auth::guard('sanctum')->user()->tokenCan('teacher-store'))
        {
            return response()->json("Unauthorized", 401);
        }
        $data = $request->validated();
        $data['senha'] = bcrypt($request->senha);
        $idUser = $this->repository->createUser($data);
    }

    public function login(Request $request)
    {
        $credentials = Auth::attempt($request->only('email', 'senha'));
        $abilities = $this->getAbilities();
        
        if ($credentials)
        {
            $userId = Auth::user()->idusuario;
            if ($this->service->isAdm($userId))
            {
                return $request->user()->createToken('token');
            }
            else
            {
                if ($this->service->isTeacher($userId))
                {
                    $abilities[] = 'challenge-store';
                    $abilities[] = 'challenge-update';
                    $abilities[] = 'challenge-destroy';
                }
                return $request->user()->createToken('token', $abilities);
            }
        }
        else
        {
            return response()->json("Dados Incorretos", 403);
        }
    }

    public function profile()
    {
        if (Auth::guard('sanctum')->check())
        {
            $data = new UserResource(Auth::guard('sanctum')->user());
            return response()->json($data, 200);
        }
        return response()->json("Unauthorized", 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('sanctum')->user()->currentAccessToken()->delete(); 
        return response()->json('Token Revogado', 200);
    }

    
}