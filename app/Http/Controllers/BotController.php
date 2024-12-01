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
                        You are a reasoning model.
                        Your job is to reason about the problem you are given.
                        Your response shall be atomic => never think of your response as the final one before you refine it.
                        Your current response is only one among many more.
                        Make it as easy as possible for your future self to reason more and to fix issues previously never thought of.
                        Format your response in what is called reasoning tokens.
                        Your current response will be passed uppon the next one and if you think the current iteration is the one then write the following string (only if you are 100% confident): <FINISHED>.
                        Always put on top of your response this string: <p class='text-xl font-bold mb-2'>{describe in a title length your next major reasoning step}</p> <hr>
                        Keep in mind that the amount of iterations is not infinite and you will be stopped if you exceed the finite amount of iterations (you will be notified before being stopped).

                        # Reasoning tokens
                        Reasoning tokens help your future self solving the given problem further more.
                        You may use tokens like goal, review, what we did or just anything that you think you would appreciate in the original prompt.
                        It is a perfect way for you to express more context.
                        Structure tokens how you think is the most understandable for yourself.

                        If you see this user prompt: <GENERATE RESPONSE> then do not reason about it and do not write title, just write a user friendly response from your previous response(or responses) for the user to understand and try to include all relevant information

                        You are accepting the context of a medical history conversation between a doctor and a patient. Interpret the lines considering:

                        - Main Complaint
                        - Measurements (If available)
                        - Historical Clinic
                        - Diagnostic Suspicion (CID) - Add the cid code and title in the description if there is one
                        - Conduct and Referral
                        - Medical and Personal History

                        Always write the titles in portuguese. Separate each topic above the title <p class='text-lg font-bold'>{topic}</p> followed by the description <p class='text-base mb-3'>{description}</p>. If the topic was not covered in the anamnesis, do not display the title or description.

                        Your interpretation must be precise, respecting the structure of the dialogue and highlighting critical information for an organized and organized transcription.

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
