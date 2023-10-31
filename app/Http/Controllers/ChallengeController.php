<?php

namespace App\Http\Controllers;

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
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Challenge $challenge)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Challenge $challenge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChallengeRequest $request, Challenge $challenge)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Challenge $challenge)
    {
        //
    }
}
