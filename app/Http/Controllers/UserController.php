<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Models\Project;
use App\Models\Invitation;
use App\Models\Challenge;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\ProjectResource;
use App\Http\Resources\V1\ChallengeResource;
use App\Http\Resources\V1\NotificationsResource;
use App\Http\Resources\V1\InvitationResource;
use Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DateService;
use App\Services\AuthService;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private $project;
    private $dateService;
    private $authService;

    public function __construct(
        protected User $repository,
    ) {
        $this->middleware('auth:sanctum')->only(['update', 'destroy']);
        $this->project = new Project();
        $this->dateService = new DateService();
        $this->authService = new AuthService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchQuery = $request->query('user');
        $searchQueryLimit = $request->query('limit') ?: 7;
        if (!empty($searchQuery)) {
            $users = $this->repository->where('apelido', 'LIKE', '%' . $searchQuery . '%')->limit($searchQueryLimit)->get();
            return UserResource::collection($users);
        }
        $users = $this->repository->paginate($searchQueryLimit);
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['senha'] = bcrypt($request->senha);
        $user = $this->repository->createUser($data);
        $token = $user->createToken('token', $this->authService->abilities());
        return $token;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $apelido)
    {
        $user = User::where('apelido', $apelido)->first();
        if (!$user) return response()->json(['message' => 'Usuário não encontrado'], 404);
        $data = [
            'nome' => $user->nome,
            'apelido' => $user->apelido,
            'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
            'criadoEm' => DateService::transformDateHumanReadable($user->data_criacao),
            'status' => $user->status
        ];
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $username)
    {
       try {
        if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()) {
            $user = Auth::guard('sanctum')->user();
            $data = $request->validated();
            if ($user->status !== 'ativo') {
                throw new HttpException(401, 'Conta desativada, ative-a para editar seu perfil');
            }
            $tt = $this->repository->updateUser($data, $user->idusuario);
            return response()->json(['message' => 'Dados atualizados'], 200);
        }
        throw new HtppException(401, 'Autorização negada');
        } catch (HttpException $e) {
           return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
       }
    }

    public function avatar(Request $request) {
        try {
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();
                $avatar = $request->validate([
                    'avatar' => 'required|image|max:1024'
                ]);
                $savedAvatar = Storage::disk('public')->putFile('avatars', $avatar['avatar']);
                $this->repository->where('idusuario', $user->idusuario)->update(['avatar' => $savedAvatar]);
                return response()->json(['message' => 'Avatar salvo com sucesso'], 200);
            }
            throw new HttpException(401, 'Autorização negada');
        } catch (HttpException $th) {
            return response()->json(['message' => $th->getMessage()], $th->getStatusCode());
        }
    }

    public function disableAccount()
    {
        try {
            if (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user()->idusuario;
                $delete = $this->repository->disable($user);
                AuthController::logout();
                return response()->json(['message' => 'Conta desativada'], 200);
            }
            throw new HttpException(401, 'Autorização negada');
        } catch (HttpException $th) {
            return response()->json(['message' => $th->getMessage()], $th->getStatusCode());
        }
    }

    public function changePassoword(Request $request)
    {
        if (Auth::guard('sanctum')->check()) {
            $validated = $request->validate([
                'senha' => 'required|min:6|max:255'
            ]);
            $user = Auth::guard('sanctum')->user()->idusuario;
            $this->repository->updateUser(['senha' => bcrypt($validated['senha'])], $user);
            return response()->json(['message' => 'Senha atualizada'], 200);
        }
        return response()->json(['message' => 'Autorização negada'], 401);
    }

    public function getProjects($username)
    {   
        $user = User::where('apelido', $username)->first();
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        
        $projects = $user->project()->with(['user'])->where('status', '1')->paginate();
        $participatedProjects = $user->permission()->with(['project.user'])->paginate()->pluck('project');

        if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()->idusuario === $user->idusuario) {
            $projects = $user->project()->with(['user'])->paginate();
        }
        
        $allProjects = $projects->merge($participatedProjects)->sortByDesc('data_projeto');
        return collect(ProjectResource::collection($allProjects))->paginate();
    }

    public function getInvites($username)
    {   
        $user = User::where('apelido', $username)->first();
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }
        $invites = $user->invite()->with('user')->orderBy('data_convite', 'DESC')->paginate();
        return InvitationResource::collection($invites);
    }

    public function getCountInvitesAndProjects(Request $request)
    {
        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            return response()->json([
                'convites' => Invitation::where('idusuario', $user->idusuario)->count(),
                'projetos' => Project::where('idusuario', $user->idusuario)->count()
            ], 200);
        }
        return response()->json(['message' => 'Autorização negada'], 401);
    }

    public function getChallengesPerfomed(Request $request)
    {
        try {
            if (Auth::guard('sanctum')->check()) {
                $teacher = $request->query('teacher');
                $idTeacher = $this->repository->getByNickname($teacher);
                if (!$idTeacher) {
                    throw new HttpException(404, 'Professor não encontrado');
                }
                $user = Auth::guard('sanctum')->user()->idusuario;
                $challenges = Challenge::with('project')->where('idusuario', $idTeacher->idusuario)->whereHas('project', function ($query) use ($user) {
                    $query->where('idusuario', $user);
                })->get();
                return ChallengeResource::collection($challenges);
            }
        } catch (HttpException $th) {
            return response()->json(['message' => $th->getMessage()], $th->getStatusCode());
        }
        
    }

    public function getNotifications() 
    {
        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user()->idusuario;
            $notifications = $this->repository->notifications($user);
            return NotificationsResource::collection($notifications);
        }
    }
}