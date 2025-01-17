<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\DateService;
use Illuminate\Support\Facades\Storage;

class InvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $author = $this->whenLoaded('user');

        $data =  [
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'dataCriacao' => DateService::transformDateHumanReadable($this->data_convite),
            'slug' => $this->slug,
            'autor' => ['nome' => $author->nome, 'apelido' => $author->apelido, 'avatar' => $author->avatar ? Storage::url($author->avatar) : null]
        ];

        if ($this->relationLoaded('participants')) {
            $data['participantes'] = $this->participants->map(function ($participant) {
                return [
                    'nome' => $participant->sender->nome,
                    'apelido' => $participant->sender->apelido,
                    'avatar' => $participant->sender->avatar
                ];
            });
        }

        return $data;
    }

}