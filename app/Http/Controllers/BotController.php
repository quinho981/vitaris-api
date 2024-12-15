<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LucianoTonet\GroqLaravel\Facades\Groq;

class BotController extends Controller
{
    public function generateAnamnese(Request $request) {
        $response = Groq::chat()->completions()->create([
            'model' => 'llama-3.1-70b-versatile',
            'messages' => [
                [
                    'role' => 'user', 
                    'content' => "
                        Interpret the following medical consultation transcription, extract the information, 
                        and organize it into structured sections. Follow the format below:

                        **Title:** Generate a concise title that summarizes the essence of the anamnese and include it in the following tag:
                        <p class='text-xl font-bold mb-2'>{title}</p> <hr>

                        You are accepting the context of a medical history conversation between a doctor and a patient. Interpret the lines considering:

                        - Main Complaint
                        - Anthropometric Measurements (If available)
                        - Historical Clinic
                        - Diagnostic Suspicion (CID) - Add the cid code and title in the description between <i> tags, if any
                        - Conduct and Referral
                        - Medical and Personal History

                        Always write the titles in portuguese. Separate each topic above the title <p class='text-lg font-bold'>{topic}</p> 
                        followed by the description <p class='text-base mb-3'>{description}</p>. If the topic was not covered in the anamnesis, 
                        do not display the title or description.

                        Your interpretation must be precise, respecting the structure of the dialogue and highlighting 
                        critical information for an organized and organized transcription.

                        Context of the anamnesis:                        
                        {$request->input('anamnese')}

                        Always respond in Portuguese.
                    "
                ],
            ],
        ]);
         
        return response()->json([
            'content' => $response['choices'][0]['message']['content']
        ]);
    }
}
