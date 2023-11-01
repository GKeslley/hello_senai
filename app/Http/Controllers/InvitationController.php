<?php

namespace App\Http\Controllers;

use Auth;
use App\Http\Resources\V1\InvitationResource;
use App\Http\Requests\StoreInvitationRequest;
use App\Http\Requests\UpdateInvitationRequest;
use App\Models\Invitation;
use App\Services\InvitationService;

class InvitationController extends Controller
{
    private $service;

    public function __construct(
        protected Invitation $repository
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
    public function store(StoreInvitationRequest $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (Auth::guard('sanctum')->check() && $user->tokenCan('invite-store'))
        {
            $data = $request->validated();
            $userId = $user->idusuario;
            $slug = $this->service->generateSlug($data['titulo']);

            $data['idusuario'] = $userId;
            $data['slug'] = $slug;
            
            if (!$this->repository->createInvitation($data))
            {
                return response()->json(['message' => 'Não Foi Possível Realizar Essa Ação'], 403);
            };       
            return response()->json(['message' => 'Convite Criado'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvitationRequest $request, $slug)
    {
        $invitation = $this->service->getBySlug($slug);
        $user = Auth::guard('sanctum')->user();
        
        //VERIFICAR SE O USUÁRIO QUE POSTOU É O MESMO QUE ATUALIZARÁ
        if (Auth::guard('sanctum')->check() && $user->tokenCan('invite-update') && $user->apelido == $invitation->user->apelido)
        {
            $data = $request->validated();
            $data['idconvite'] = $invitation->idconvite;

            if ($data['titulo'] != $invitation->titulo)
            {
                $data['slug'] = $this->service->generateSlug($data['titulo']);
            }
            
            if (!$this->repository->updateInvitation($data))
            {
                return response()->json(['message' => 'Não Foi Possível Realizar Essa Ação'], 403);
            };       
            return response()->json(['message' => 'Convite Atualizado'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug)
    {
        $invitation = $this->service->getBySlug($slug);
        $user = Auth::guard('sanctum')->user();
        
        if (Auth::guard('sanctum')->check() && $user->tokenCan('invite-update') && $user->apelido == $invitation->user->apelido)
        {
            if (!$this->repository->deleteInvitation($invitation->idconvite))
            {
                return response()->json(['message' => 'Não Foi Possível Realizar Essa Ação'], 403);
            };       
            return response()->json(['message' => 'Convite Excluido'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }
}