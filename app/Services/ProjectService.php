<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Project;

class ProjectService implements ISlugService
{
    public function generateSlug($name)
    {
      $slug = DB::select("SELECT createUniqueSlug(?, ?) AS slug", [$name, 'projeto'])[0]->slug;
      return $slug;
    }
    public function getBySlug($slug)
    {
      if ($data = Project::with('user')->where('slug', '=', $slug)->first())
      {
        return $data;
      };
      return null;
    }
}