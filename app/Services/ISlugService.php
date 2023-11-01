<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

interface ISlugService
{
    public function generateSlug($name);

    public function getBySlug($slug);
}