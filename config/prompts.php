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
];