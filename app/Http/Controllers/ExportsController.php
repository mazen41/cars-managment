<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportsController extends Controller
{

    public function __construct(){
        $this->middleware(['permission:export_users'])->only('download');
    }
    /**
     * Handle the export request.
     *
     * @param  string  $type
     * @param  array|null  $ids
     * @return StreamedResponse
     */
    public function download($file)
{
    $path = 'all/' . $file;

    if (!Storage::disk('exports')->exists($path)) {
        abort(404);
    }

    return Storage::disk('exports')->download($path);
}

}

