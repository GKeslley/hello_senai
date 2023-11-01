<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Invitation;

class InvitationService implements ISlugService
{
    public function generateSlug($name)
    {
      $slug = DB::select("SELECT createUniqueSlug(?, ?) AS slug", [$name, 'convite'])[0]->slug;
      return $slug;
    }
    public function getBySlug($slug)
    {
      if ($data = Invitation::with('user')->where('slug', '=', $slug)->first())
      {
        return $data;
      };
      return null;
    }
}