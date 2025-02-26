<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900 dark:text-gray-100">
        Dashboard
      </h2>
    </template>

    <!-- Page Wrapper with Light & Dark Classes -->
    <div class="py-12 bg-gray-50 dark:bg-gray-900 transition-colors duration-300 min-h-screen">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Card Container -->
        <div class="overflow-hidden bg-white dark:bg-zinc-800 shadow-sm sm:rounded-lg">
          <!-- Card Content -->
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div>
              <!-- Styled Button -->
               <!-- Create a dropdown menu to select the language I speak default english-->
               <div>
                <label
                  for="sourceLang"
                  class="block mb-1 font-medium text-sm text-gray-700 dark:text-gray-300"
                >
                  I speak:
                </label>
                <select
                  id="sourceLang"
                  v-model="sourceLanguage"
                  class="block w-full mt-1 rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-700 dark:text-gray-100 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                <option value="Spanish">Spanish</option>
                  <option value="English">English</option>
                  <option value="French">French</option>
                  <option value="German">German</option>
                  <!-- Add as many languages as you wish -->
                </select>
              </div>

               <!-- Create a dropdown menu to select the language I desire to translate default spanish-->
               <div>
                <label
                  for="targetLang"
                  class="block mb-1 font-medium text-sm text-gray-700 dark:text-gray-300"
                >
                  Translate to:
                </label>
                <select
                  id="targetLang"
                  v-model="targetLanguage"
                  class="block w-full mt-1 rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-700 dark:text-gray-100 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                  <option value="Spanish">Spanish</option>
                  <option value="English">English</option>
                  <option value="French">French</option>
                  <option value="German">German</option>
                  <!-- Add as many languages as you wish -->
                </select>
              </div>

              <br>
              <br>

              <div class="flex items-center justify-between">
                <div >
                  <button
                    @mousedown="startRecording"
                    @mouseup="stopRecording"
                    class="rounded-full bg-gray-200 px-4 py-2 text-black transition-colors duration-300 
                              hover:bg-gray-300 dark:bg-zinc-700 dark:text-white dark:hover:bg-zinc-600
                              focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                  🎤 Hold to Speak
                  </button>
                </div>

                <div>
                  <div v-if="isRecording" class="flex items-center space-x-2">
                    <div class="relative flex">
                      <!-- Expanding ring (the ping) -->
                      <div class="absolute h-full w-full rounded-full bg-red-300 opacity-75 animate-ping"></div>
                      <!-- Center icon -->
                      <div class="relative flex h-12 w-12 items-center justify-center rounded-full bg-red-500 text-white">
                        🎤
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <!-- Translating Animation (Shown Only While isTranslating = true) -->
                  <div v-if="isTranslating" class="flex items-center space-x-2">
                    <!-- Pulsing Globe Container -->
                    <div class="relative flex">
                      <!-- Expanding ring (the ping) -->
                      <div class="absolute h-full w-full rounded-full bg-green-300 opacity-75 animate-ping"></div>
                      <!-- Center icon -->
                      <div class="relative flex h-8 w-8 items-center justify-center rounded-full bg-green-500 text-white">
                        🌐
                      </div>
                    </div>

                    <!-- “Translating...” Text -->
                    <span class="font-medium text-green-600 dark:text-green-400">Translating...</span>
                  </div>
                </div>

              </div>

            

             


              <!-- Messages List -->
              <ul class="mt-4 space-y-2">
                <li
                  v-for="msg in sortedMessages"
                  :key="msg.id"
                  :class="[
                    'p-3', 
                    'rounded-lg', 
                    'transition-colors', 
                    'duration-300',
                    msg.speaker === 'Translated' 
                      ? 'bg-blue-100 dark:bg-blue-900' 
                      : 'bg-gray-100 dark:bg-zinc-700'
                  ]"
                >
                <audio
                  v-if="msg.audioUrl"
                  :src="msg.audioUrl"
                  controls
                ></audio>
                <span v-else>
                  <b>[{{ formatTimestamp(msg.timestamp) }}] {{ msg.speaker }}:</b> {{ msg.text }}
                </span>
                  
      
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import Pusher from 'pusher-js';
import RecordRTC from 'recordrtc';
import dayjs from 'dayjs';

window.Pusher = Pusher;

export default {
  data() {
    return {
      mediaRecorder: null,
      audioChunks: [],
      messages: [],
      userId: Math.random().toString(36).substring(2, 7), // Unique session ID
      // language options
      sourceLanguage: 'English',   // defaults to English
      targetLanguage: 'Spanish',   // defaults to Spanish
      isRecording: false,
      isTranslating: false,
      audioSource: null,
    };
  },
  computed: {
    sortedMessages() {
      return [...this.messages].sort((a, b) => a.timestamp - b.timestamp);
    },
  
  },
  mounted() {
    
    // Subscribe to private channel
    window.Echo.private(`translations.${this.userId}`)
      .listen('TranslationProcessed', (event) => {
        this.isTranslating = false;

        
        this.messages.push({
          id: event.timestamp,
          speaker: 'Transcription',
          text: event.originalText,
          timestamp: event.timestamp,
        });
        
        
        this.messages.push({
          id: event.timestamp,
          speaker: 'Translated',
          text: event.translatedText,
          timestamp: event.timestamp,
        });
      });
  },
  methods: {
    
    formatTimestamp(ts) {
      return dayjs(ts).format('MMM DD, YYYY h:mm:ss A');
    },
    async startRecording() {
      if(this.sourceLanguage == this.targetLanguage){
        alert('Please select different languages for source and target');
        return;
      }

      this.isRecording = true;
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      // Set up RecordRTC for WAV (PCM)
      this.recorder = new RecordRTC(stream, {
        type: 'audio',
        mimeType: 'audio/wav',
        recorderType: RecordRTC.StereoAudioRecorder,
        numberOfAudioChannels: 1,  // 1 = mono, 2 = stereo
        // sampleRate: 16000,         // Adjust as needed; 16k often enough for speech
      });

      // Start recording
      this.recorder.startRecording();
    },
    stopRecording() {
      this.isRecording = false;
      this.isTranslating = true;
      this.recorder.stopRecording(async () => {
        const audioBlob = this.recorder.getBlob();

        // Prepare form data
        const formData = new FormData();
        formData.append('audio', audioBlob, 'recorded.wav');
        formData.append('timestamp', Date.now());
        formData.append('userId', this.userId);
        // Pass selected languages to the backend
        formData.append('sourceLang', this.sourceLanguage);
        formData.append('targetLang', this.targetLanguage);


        // Send to backend
        const response = await fetch('/api/process-audio', {
          method: 'POST',
          body: formData,
        });


        const result = await response.json();
        this.audioSource = result.streamUrl;
        console.log(result);

        this.messages.push({
          id: Date.now(),
          speaker: 'You',
          // Insert the actual URL in the audio tag, not a Vue binding
          text: "",
          audioUrl: result.streamUrl,
          timestamp: Date.now(),
        });
  
      });
    },
  },
};
</script>
