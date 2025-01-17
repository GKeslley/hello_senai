<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\DateService;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->whenLoaded('user');
        $project = $this->whenLoaded('project');

        $data = [
            'desafio' => [
                'titulo' => $this->titulo, 
                'descricao' => $this->descricao, 
                'dataCriacao' => DateService::transformDateHumanReadable($this->data_criacao), 
                'slug' => $this->slug,
                'imagem' => $this->imagem ? Storage::url($this->imagem) : null
            ]
        ];

        if ($this->relationLoaded('user')) {
            $data['autor'] = ['nome' => $user->nome, 'apelido' => $user->apelido, 'avatar' => $user->avatar ? Storage::url($user->avatar) : null];
        }

        if ($this->relationLoaded('project')) {
            $data['projeto'] = ['slug' => $project->slug];
        }
        
        return $data;
    }
}