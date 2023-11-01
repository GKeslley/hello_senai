<?php

namespace App\Http\Controllers;

use App\Http\Resources\V1\InvitationResource;
use Auth;
use App\Models\Challenge;
use App\Http\Requests\StoreChallengeRequest;
use App\Http\Requests\UpdateChallengeRequest;
use App\Services\InvitationService;

class ChallengeController extends Controller
{
    private $service;
    public function __construct(
        protected Challenge $repository
    )
    {
        $this->service = new InvitationService();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invitations = $this->repository->with('user')->paginate();
        return InvitationResource::collection($invitations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChallengeRequest $request)
    {
        if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()->tokenCan('challenge-store'))
        {
            $data = $request->validated();
            $teacherId = Auth::guard('sanctum')->user()->idusuario;
            $data['idusuario'] = $teacherId;

            $slug = $this->service->generateSlug($data['titulo']);
            $data['slug'] = $slug;

            if (!$this->repository->createChallenge($data, $teacherId))
            {
                return response()->json(['message' => 'Não Foi Possivel Realizar Essa Ação', 403]);
            };
            return response()->json(['message' => 'Desafio Criado', 200]);
        }
 
        return response()->json(['message' => 'Unauthorized', 401]);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $data = $this->service->getBySlug($slug);
        if (!$data)
        {
            return response()->json(['message' => 'Desafio Não Encontrado'], 404);
        }
        return new InvitationResource($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChallengeRequest $request, $slug)
    {
        $invitation = $this->service->getBySlug($slug);
        $user = Auth::guard('sanctum')->user();
        
        //VERIFICAR SE O USUÁRIO QUE POSTOU É O MESMO QUE ATUALIZARÁ
        if (Auth::guard('sanctum')->check() && $user->tokenCan('challenge-update') && $user->apelido == $invitation->user->apelido)
        {
            $data = $request->validated();
            $data['idconvite'] = $invitation->idconvite;

            if ($data['titulo'] != $invitation->titulo)
            {
                $data['slug'] = $this->service->generateSlug($data['titulo']);
            }

            if (!$this->repository->updateChallenge($data))
            {
                return response()->json(['message' => 'Não Foi Possível Realizar Essa Ação'], 403);
            };       
            return response()->json(['message' => 'Desafio Atualizado'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug)
    {
        $invitation = $this->service->getBySlug($slug);
        
        if (!$invitation)
        {
            return response()->json(['message' => 'Desafio Não Encontrado'], 404);
        }
        
        $user = Auth::guard('sanctum')->user();
        
        if (Auth::guard('sanctum')->check() && $user->tokenCan('challenge-destroy') && $user->apelido == $invitation->user->apelido)
        {
            if (!$this->repository->deleteChallenge($invitation->idconvite))
            {
                return response()->json(['message' => 'Não Foi Possível Realizar Essa Ação'], 403);
            };       
            return response()->json(['message' => 'Convite Excluido'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
