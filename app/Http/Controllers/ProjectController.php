<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Auth;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\V1\ProjectResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectController extends Controller
{
    private $service;
    private $authService;
    private $users;

    public function __construct(
        protected Project $repository
    )
    {
        $this->service = new ProjectService();
        $this->authService = new AuthService();
        $this->users = new User();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = $this->repository->with('user')->paginate();
        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (Auth::guard('sanctum')->check() && $user->tokenCan('project-store'))
        {
            $data = $request->validated();
            $dataClone = $data;
            unset($data['participantes']);

            $userId = $user->idusuario;
            $slug = $this->service->generateSlug($data['nome_projeto']);
            
            $data['idusuario'] = $userId;
            $data['slug'] = $slug;
            
            if ($projectId = $this->repository->createProject($data))
            {
                $participants = $dataClone['participantes'];
                if ($participants)
                {
                    try {
                        $dataParticipants = $this->addParticipants($participants, $projectId);
                        $this->repository->addRangeParticipants($dataParticipants);
                    } catch (\Exception $message) {
                        return response()->json(['message' => $message->getMessage()], 404);
                    }
                }
    
                return response()->json(['message' => 'Projeto Criado'], 200);
            };    

            return response()->json(['message' => 'Não Foi Possível Realizar Essa Ação'], 403);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $data = $this->service->getBySlug($slug);
        if (!$data)
        {
            return response()->json(['message' => 'Projeto Não Encontrado'], 404);
        }
        return new ProjectResource($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, $slug)
    {
        $project = $this->service->getBySlug($slug);
        $user = Auth::guard('sanctum')->user();

        //VERIFICAR SE O USUÁRIO QUE POSTOU É O MESMO QUE ATUALIZARÁ
        if (Auth::guard('sanctum')->check() && $user->tokenCan('project-update') 
        && ($user->apelido == $project->user->apelido || $this->authService->isRedator($user->idusuario)))
        {
            $data = $request->validated();
            $data['idprojeto'] = $project->idprojeto;

            if ($data['nome_projeto'] != $project->nome_projeto)
            {
                $data['slug'] = $this->service->generateSlug($data['nome_projeto']);
            };

            if (!$this->repository->updateProject($data))
            {
                return response()->json(['message' => 'Não Foi Possível Realizar Essa Ação'], 403);
            }; 

            return response()->json(['message' => 'Projeto Atualizado'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug)
    {
        $project = $this->service->getBySlug($slug);
        $user = Auth::guard('sanctum')->user();
        
        if (Auth::guard('sanctum')->check() && $user->tokenCan('project-update') && $user->apelido == $project->user->apelido)
        {
            if (!$this->repository->deleteProject($project->idprojeto))
            {
                return response()->json(['message' => 'Não Foi Possível Realizar Essa Ação'], 403);
            };       
            return response()->json(['message' => 'Projeto Excluido'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function addParticipants($participants, $projectId)
    {
        foreach ($participants as $key => $value) {
            $nickname = $this->users->getByNickname($value['apelido']);
            if (!$nickname) 
            {
                throw new \Exception("Usúario Não Encontrado", 404);        
            };
            $permission = $this->repository->getUserPermissionInProject($value['permissao']);

            $iduser = $nickname[0]['idusuario'];
            $data[] = [
                'idusuario' => $iduser,
                'idprojeto' => $projectId,
                'permissao' => $permission
            ];
        }
        return $data;
    }
}