<?php

return [
    "anamnesis" => "
        Interpret the following medical consultation transcription, extract the information, 
        and organize it into structured sections. Follow the format below:

        **Title:** Generate a concise title that summarizes the essence of the anamnese and include it in the following tag:
        <h2><strong>{title}</strong></h2><p></p>

        You are accepting the context of a medical history conversation between a doctor and a patient. Interpret the lines considering:

        - Main Complaint
        - Anthropometric Measurements (If available)
        - Historical Clinic
        - Diagnostic Suspicion (CID)
            - Separate each cid with: <li>{code:cid}</li> If exists. Do not add text between cids, only code and name that refers cid
        - Conduct and Referral
        - Medical and Personal History
        - Orientation
            - Separate each orientation with: <li>{orientation}</li>
            
        Always write the titles in portuguese. Separate each topic above the title <h3><strong>{topic}</strong></h3> 
        followed by the description <p>{description}</p><p></p>
        If the topic was not covered in the anamnesis, 
        do not display the title or description.

        Your interpretation must be precise, respecting the structure of the dialogue and highlighting 
        critical information for an organized and organized transcription.

        Context of the anamnesis:                        
        {context}

        Always respond in Portuguese.
    ",
    "ai_insights" => "
        You are a medical decision support assistant.
        Based on the provided text, extract and organize the information into a **single JSON object** called `medical_analysis`:

        {
            'medical_analysis': {
                'main_topics': ['topic', 'topic2', 'topic3'],
                'identified_symptoms': ['symptoms', 'symptoms2', 'symptoms3'],
                'possible_diagnoses': ['diagnoses', 'diagnoses2', 'diagnoses3'],
                'brief_description': ['description'],
            }
        }

        IMPORTANT:
        - Respond **only in valid JSON**, without additional text.
        - Use **the keys exactly as above**.
        - Always write in Portuguese.
        - Each value must be **an array of strings**, even if there is only one item.

        Text for Analysis:
        {context}

        Always respond in Portuguese.
    ",
];