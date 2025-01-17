<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProjectService implements ISlugService
{
    public function generateSlug($name)
    {
        $slug = DB::select("SELECT createUniqueSlug(?, ?) AS slug", [$name, 'projeto'])[0]->slug;
        return $slug;
    }
    
    public function getBySlug($slug)
    {
        $data = Project::with(['user', 'participants', 'comments.user' ,'comments.replies.user', 'challenge'])->where('slug', '=', $slug)->first();
        if (!$data) throw new HttpException(404, 'Projeto não encontrado');

        return $data;
    }

    public function isProjectStored($slug)
    {
        $project = Project::where('slug', '=', $slug)->first();
        return $project;
    }
}