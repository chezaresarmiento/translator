<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Events\TranslationProcessed;
use Illuminate\Support\Facades\Log;



class ProcessAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $timestamp;
    protected $userId;
    protected $sourcelang;
    protected $targetlang;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $timestamp, $userId, $sourcelang, $targetlang)
    {
        $this->filePath = $filePath;
        $this->timestamp = $timestamp;
        $this->userId = $userId;
        $this->sourcelang=$sourcelang;
        $this->targetlang=$targetlang;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Processing audio file: ' . $this->filePath);

        # Get the full file path
        $audioFile = Storage::disk('local')->path($this->filePath);

        Log::info('File path: ' . $audioFile);

        if (! file_exists($audioFile)) {
            Log::error('File not found: ' . $audioFile);
        }


        // if($this->sourcelang=='en'){
            Log::info('Transcribing audio file in english');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ])
            ->attach(
                'file',                             // important: must be "file"
                file_get_contents($audioFile),      // binary data
                'my-recording.wav',                 // filename
                ['Content-Type' => 'audio/wav']     // ensure "audio/wav"
            )
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'response_format' => 'json',
            ]);

            $transcription = $response->json()['text'] ?? '';

            //Translate text using ChatGPT API
            $translationResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json'
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'Translate from {$this->sourceLang} to {$this->targetLang}:'],
                    ['role' => 'user', 'content' => $transcription]
                ]
            ]);

            
            $translatedText = $translationResponse->json()['choices'][0]['message']['content'] ?? '';

        

        // if($this->sourcelang!='en'){
        //     Log::info('Translating audio file into english');
        //     $response = Http::withHeaders([
        //         'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        //     ])
        //     ->attach(
        //         'file',                             // important: must be "file"
        //         file_get_contents($audioFile),      // binary data
        //         'my-recording.wav',                 // filename
        //         ['Content-Type' => 'audio/wav']     // ensure "audio/wav"
        //     )
        //     ->post('https://api.openai.com/v1/audio/translations', [
        //         'model' => 'whisper-1',
        //         'response_format' => 'json',
        //         'input' => $this->sourcelang,
        //     ]);
        //     $transcription ="";
        //     $translatedText = $response->json()['text'] ?? '';
        // }
            
    
        // Broadcast translation via WebSockets
        broadcast(new TranslationProcessed($this->userId, $this->timestamp, $transcription, $translatedText));
    }
}
