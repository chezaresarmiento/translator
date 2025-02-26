<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessAudio;
use Illuminate\Support\Facades\Log;

class AudioController extends Controller
{
    public function processAudio(Request $request)
    {
    
        // $validations=$request->validate([
        //     'audio' => 'required|file|mimes:wav,mp3,ogg',
        //     'timestamp' => 'required|numeric',
        //     'userId' => 'required|string'
        // ]);

        // return response()->json(['message' => $validations]);
       

        $audioFile = $request->file('audio');
        $filePath = 'audio/' . uniqid() . '.' . 'wav';
        Storage::disk('local')->put($filePath, file_get_contents($audioFile));

        $sourcelang=$request->sourceLang;
        $targetlang=$request->targetLang;

        Log::info('Source Language: ' . $sourcelang);
        Log::info('Target Language: ' . $targetlang);

        // Dispatch job to process audio asynchronously
        ProcessAudio::dispatch($filePath, $request->timestamp, $request->userId,$sourcelang,$targetlang);

        $url = route('audio.stream', ['filename' => basename($filePath)]);

        return response()->json([
            'message' => 'Audio received',
            'streamUrl' => $url,
        ]);


        return response()->json([
            'message' => 'Audio received',
            'path' => $filePath,
        ]);
    }
}
