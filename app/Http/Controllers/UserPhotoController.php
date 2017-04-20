<?php

namespace App\Http\Controllers;

use App\Services\MSGraphService;

class UserPhotoController extends Controller
{
    /**
     * Get photo of the specified user.
     *
     * @param string $o365UserId The Office 365 user id of the user
     *
     * @return \Illuminate\Http\Response
     */
    public function userPhoto($o365UserId)
    {
        $msGraph = new MSGraphService();

        $stream = $msGraph->getUserPhoto($o365UserId);
        if ($stream) {
            $contents = $stream->getContents();
            $headers = [
                "Content-type" => "image/jpeg",
                "Accept-Ranges" => "bytes",
                "Content-Length" => strlen($contents)
            ];
            return response()->stream(function () use ($stream, $contents) {
                $out = fopen('php://output', 'wb');
                fwrite($out, $contents);
                fclose($out);
            }, 200, $headers);
        }else {
            return response()->file(realpath("./public/images/header-default.jpg"));
        }
    }
}
